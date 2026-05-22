#!/usr/bin/env python3

from __future__ import annotations

import argparse
import json
import pickle
from dataclasses import dataclass
from pathlib import Path
import sys

import cv2
import numpy as np
from skimage.feature import graycomatrix, graycoprops, hog, local_binary_pattern
from skimage.filters import gabor


IMG_SIZE = (128, 128)
GABOR_THETAS = [0, np.pi / 4, np.pi / 2, 3 * np.pi / 4]
GABOR_FREQUENCIES = [0.2, 0.5]
DEFAULT_LABELS = ["cardboard", "glass", "metal", "paper", "plastic"]

try:
    sys.stdout.reconfigure(encoding="utf-8")
    sys.stderr.reconfigure(encoding="utf-8")
except Exception:
    pass


@dataclass(frozen=True)
class FeatureConfig:
    hsv_bins: int
    use_hog: bool = False
    use_glcm: bool = False


def isolate_object(img_bgr: np.ndarray) -> tuple[np.ndarray, np.ndarray]:
    gray = cv2.cvtColor(img_bgr, cv2.COLOR_BGR2GRAY)
    _, thresh = cv2.threshold(gray, 0, 255, cv2.THRESH_BINARY_INV + cv2.THRESH_OTSU)
    kernel = cv2.getStructuringElement(cv2.MORPH_ELLIPSE, (7, 7))
    mask = cv2.morphologyEx(thresh, cv2.MORPH_CLOSE, kernel, iterations=2)
    masked_bgr = cv2.bitwise_and(img_bgr, img_bgr, mask=mask)
    return masked_bgr, mask


def preprocess_image(image_path: str) -> tuple[np.ndarray, np.ndarray, np.ndarray, np.ndarray]:
    img = cv2.imread(image_path)
    if img is None:
        raise FileNotFoundError(f"Cannot read image: {image_path}")

    img = cv2.resize(img, IMG_SIZE, interpolation=cv2.INTER_AREA)
    masked_bgr, mask = isolate_object(img)
    gray_masked = cv2.cvtColor(masked_bgr, cv2.COLOR_BGR2GRAY)
    hsv_masked = cv2.cvtColor(masked_bgr, cv2.COLOR_BGR2HSV)
    return masked_bgr, gray_masked, hsv_masked, mask


def compute_hsv_histogram(hsv_img: np.ndarray, mask: np.ndarray, bins: int = 32) -> np.ndarray:
    hist_h = cv2.calcHist([hsv_img], [0], mask, [bins], [0, 180])
    hist_s = cv2.calcHist([hsv_img], [1], mask, [bins], [0, 256])
    hist_v = cv2.calcHist([hsv_img], [2], mask, [bins], [0, 256])

    hist_h /= (hist_h.sum() + 1e-7)
    hist_s /= (hist_s.sum() + 1e-7)
    hist_v /= (hist_v.sum() + 1e-7)

    return np.concatenate([hist_h, hist_s, hist_v]).flatten().astype(np.float32)


def compute_lbp_histogram(gray_img: np.ndarray, mask: np.ndarray, p: int = 8, r: int = 1, bins: int = 10) -> np.ndarray:
    lbp = local_binary_pattern(gray_img, P=p, R=r, method="uniform")
    fg_pixels = lbp[mask > 0]
    hist, _ = np.histogram(fg_pixels, bins=bins, range=(0, bins))
    hist = hist.astype(np.float32)
    hist /= (hist.sum() + 1e-7)
    return hist


def compute_gabor_features(gray_img: np.ndarray) -> np.ndarray:
    feats = []
    for freq in GABOR_FREQUENCIES:
        for theta in GABOR_THETAS:
            real_response, imag_response = gabor(gray_img, frequency=freq, theta=theta)
            magnitude = np.sqrt(real_response**2 + imag_response**2)
            feats.append(float(magnitude.mean()))
            feats.append(float(magnitude.var()))
    return np.array(feats, dtype=np.float32)


def compute_hu_moments(gray_img: np.ndarray, mask: np.ndarray) -> np.ndarray:
    moments = cv2.moments(mask)
    hu_raw = cv2.HuMoments(moments).flatten()
    hu_log = -np.sign(hu_raw) * np.log10(np.abs(hu_raw) + 1e-10)
    return hu_log.astype(np.float32)


def compute_hog_features(gray_img: np.ndarray) -> np.ndarray:
    features = hog(
        gray_img,
        orientations=9,
        pixels_per_cell=(16, 16),
        cells_per_block=(2, 2),
        feature_vector=True,
    )
    return np.asarray(features, dtype=np.float32)


def compute_glcm_features(gray_img: np.ndarray) -> np.ndarray:
    gray_q = (gray_img // 4).astype(np.uint8)
    glcm = graycomatrix(
        gray_q,
        distances=[1, 3],
        angles=[0, np.pi / 4, np.pi / 2, 3 * np.pi / 4],
        levels=64,
        symmetric=True,
        normed=True,
    )

    feats = []
    for prop in ["contrast", "dissimilarity", "homogeneity", "energy", "correlation"]:
        feats.extend(graycoprops(glcm, prop).flatten())
    return np.array(feats, dtype=np.float32)


def extract_features(image_path: str, config: FeatureConfig) -> np.ndarray:
    _, gray_masked, hsv_masked, mask = preprocess_image(image_path)

    parts = [
        compute_hsv_histogram(hsv_masked, mask, bins=config.hsv_bins),
        compute_lbp_histogram(gray_masked, mask),
        compute_gabor_features(gray_masked),
        compute_hu_moments(gray_masked, mask),
    ]

    if config.use_hog:
        parts.append(compute_hog_features(gray_masked))

    if config.use_glcm:
        parts.append(compute_glcm_features(gray_masked))

    return np.concatenate(parts).astype(np.float32)


def candidate_configs() -> list[FeatureConfig]:
    return [
        FeatureConfig(32, False, False),
        FeatureConfig(64, False, False),
        FeatureConfig(128, False, False),
        FeatureConfig(64, True, False),
        FeatureConfig(64, True, True),
        FeatureConfig(128, True, True),
    ]


def hog_vector_length() -> int:
    sample = np.zeros(IMG_SIZE, dtype=np.uint8)
    return int(len(compute_hog_features(sample)))


def glcm_vector_length() -> int:
    return 40


def feature_length(config: FeatureConfig) -> int:
    length = (config.hsv_bins * 3) + 10 + 16 + 7
    if config.use_hog:
        length += hog_vector_length()
    if config.use_glcm:
        length += glcm_vector_length()
    return length


def load_pickle(path: Path):
    with path.open("rb") as handle:
        return pickle.load(handle)


def resolve_model_bundle(model_dir: Path) -> tuple[Path, Path, Path | None]:
    classifier_candidates = [
        model_dir / "best_ml_model.pkl",
        model_dir / "svm_model.pkl",
    ]
    scaler_candidates = [
        model_dir / "best_ml_scaler.pkl",
        model_dir / "scaler.pkl",
    ]
    encoder_candidates = [
        model_dir / "label_encoder.pkl",
    ]

    classifier = next((path for path in classifier_candidates if path.exists()), None)
    scaler = next((path for path in scaler_candidates if path.exists()), None)
    encoder = next((path for path in encoder_candidates if path.exists()), None)

    if classifier is None or scaler is None:
        raise FileNotFoundError(
            "Missing model/scaler files. Put best_ml_model.pkl or svm_model.pkl, and best_ml_scaler.pkl or scaler.pkl in storage/app/model."
        )

    return classifier, scaler, encoder


def guess_config(expected_features: int, override: FeatureConfig | None = None) -> FeatureConfig:
    if override is not None:
        if feature_length(override) != expected_features:
            raise ValueError(
                f"Override config does not match scaler input size ({expected_features})."
            )
        return override

    for config in candidate_configs():
        if feature_length(config) == expected_features:
            return config

    raise ValueError(
        f"No feature config matches scaler input size {expected_features}."
    )


def softmax_from_scores(scores: np.ndarray) -> np.ndarray:
    scores = np.asarray(scores, dtype=np.float64)
    if scores.ndim == 1:
        scores = scores.reshape(1, -1)
    scores = scores - np.max(scores, axis=1, keepdims=True)
    exp_scores = np.exp(scores)
    return exp_scores / np.sum(exp_scores, axis=1, keepdims=True)


def predict(image_path: str, model_dir: Path, override: FeatureConfig | None = None) -> dict:
    classifier_path, scaler_path, encoder_path = resolve_model_bundle(model_dir)
    classifier = load_pickle(classifier_path)
    scaler = load_pickle(scaler_path)
    label_encoder = load_pickle(encoder_path) if encoder_path is not None else None

    expected_features = getattr(scaler, "n_features_in_", None)
    if expected_features is None:
        raise RuntimeError("Scaler pickle has no n_features_in_ attribute.")

    config = guess_config(int(expected_features), override)
    feature_vector = extract_features(image_path, config).reshape(1, -1)

    if feature_vector.shape[1] != int(expected_features):
        raise RuntimeError(
            f"Input vector has {feature_vector.shape[1]} features but scaler expects {expected_features}."
        )

    # Sanitize features: replace NaN/Inf and clip extreme values to avoid numeric overflows
    feature_vector = np.nan_to_num(feature_vector, nan=0.0, posinf=1e6, neginf=-1e6)
    feature_vector = np.clip(feature_vector, -1e6, 1e6)

    try:
        scaled = scaler.transform(feature_vector)
    except Exception as e:
        # Provide more context when scaling fails
        raise RuntimeError(f"Scaler transform failed: {e}") from e
    raw_prediction = classifier.predict(scaled)[0]

    if label_encoder is not None and isinstance(raw_prediction, (int, np.integer)):
        label = label_encoder.inverse_transform([int(raw_prediction)])[0]
    elif isinstance(raw_prediction, (int, np.integer)):
        label = DEFAULT_LABELS[int(raw_prediction)] if int(raw_prediction) < len(DEFAULT_LABELS) else str(int(raw_prediction))
    else:
        label = str(raw_prediction)

    confidence = None
    probabilities = None
    if hasattr(classifier, "predict_proba"):
        probabilities = classifier.predict_proba(scaled)[0].astype(float).tolist()
        confidence = float(max(probabilities))
    elif hasattr(classifier, "decision_function"):
        scores = classifier.decision_function(scaled)
        probabilities = softmax_from_scores(np.asarray(scores))[0].astype(float).tolist()
        confidence = float(max(probabilities))

    return {
        "status": "ok",
        "label": label,
        "confidence": confidence,
        "probabilities": probabilities,
        "model_file": classifier_path.name,
        "scaler_file": scaler_path.name,
        "feature_config": {
            "hsv_bins": config.hsv_bins,
            "use_hog": config.use_hog,
            "use_glcm": config.use_glcm,
        },
    }


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(description="Predict waste class for a single image.")
    parser.add_argument("--image", required=True, help="Absolute path to the image file.")
    parser.add_argument("--model-dir", required=True, help="Directory containing pickle artifacts.")
    parser.add_argument("--hsv-bins", type=int, default=None, help="Override HSV histogram bins.")
    parser.add_argument("--use-hog", action="store_true", help="Force HOG features.")
    parser.add_argument("--use-glcm", action="store_true", help="Force GLCM features.")
    return parser.parse_args()


def main() -> int:
    args = parse_args()
    try:
        override = None
        if args.hsv_bins is not None:
            override = FeatureConfig(args.hsv_bins, args.use_hog, args.use_glcm)

        result = predict(args.image, Path(args.model_dir), override)
        print(json.dumps(result, ensure_ascii=False))
        return 0
    except Exception as exception:
        print(json.dumps({"status": "error", "error": str(exception)}, ensure_ascii=True))
        return 1


if __name__ == "__main__":
    raise SystemExit(main())