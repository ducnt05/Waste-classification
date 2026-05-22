<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Waste Vision Studio</title>
    <style>
    :root {
        --bg-1: #08131f;
        --bg-2: #10283f;
        --panel: rgba(8, 19, 31, 0.74);
        --panel-border: rgba(255, 255, 255, 0.12);
        --text: #f4f7fb;
        --muted: #b9c7d8;
        --accent: #49d49d;
        --accent-2: #6ec8ff;
        --danger: #ff7d7d;
    }

    * {
        box-sizing: border-box;
    }

    body {
        margin: 0;
        font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        color: var(--text);
        min-height: 100vh;
        background:
            radial-gradient(circle at top left, rgba(73, 212, 157, 0.18), transparent 30%),
            radial-gradient(circle at right center, rgba(110, 200, 255, 0.16), transparent 32%),
            linear-gradient(160deg, var(--bg-1), var(--bg-2));
    }

    .wrap {
        width: min(1180px, calc(100% - 32px));
        margin: 0 auto;
        padding: 32px 0 48px;
    }

    .hero {
        display: grid;
        grid-template-columns: 1.15fr 0.85fr;
        gap: 24px;
        align-items: stretch;
        margin-bottom: 24px;
    }

    .panel {
        background: var(--panel);
        border: 1px solid var(--panel-border);
        border-radius: 24px;
        box-shadow: 0 24px 70px rgba(0, 0, 0, 0.32);
        backdrop-filter: blur(18px);
    }

    .hero-copy {
        padding: 36px;
        position: relative;
        overflow: hidden;
    }

    .hero-copy::after {
        content: "";
        position: absolute;
        inset: auto -50px -70px auto;
        width: 220px;
        height: 220px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(73, 212, 157, 0.25), transparent 68%);
        pointer-events: none;
    }

    .eyebrow {
        display: inline-flex;
        gap: 8px;
        align-items: center;
        padding: 8px 12px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.08);
        color: var(--muted);
        font-size: 13px;
        letter-spacing: 0.02em;
        margin-bottom: 16px;
    }

    h1 {
        margin: 0 0 12px;
        font-size: clamp(2.4rem, 5vw, 4.8rem);
        line-height: 0.98;
        letter-spacing: -0.05em;
        max-width: 11ch;
    }

    .lede {
        margin: 0;
        color: var(--muted);
        font-size: 1.02rem;
        line-height: 1.7;
        max-width: 62ch;
    }

    .stats {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
        margin-top: 28px;
    }

    .stat {
        padding: 16px;
        border-radius: 18px;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.08);
    }

    .stat .value {
        display: block;
        font-size: 1.35rem;
        font-weight: 700;
        margin-bottom: 6px;
    }

    .stat .label {
        color: var(--muted);
        font-size: 0.9rem;
    }

    .form-panel {
        padding: 28px;
    }

    .form-title {
        margin: 0 0 10px;
        font-size: 1.2rem;
    }

    .form-note {
        margin: 0 0 18px;
        color: var(--muted);
        line-height: 1.6;
    }

    .upload-box {
        border: 1.5px dashed rgba(255, 255, 255, 0.18);
        border-radius: 20px;
        padding: 18px;
        background: rgba(255, 255, 255, 0.03);
        margin-bottom: 18px;
    }

    .upload-box input {
        width: 100%;
        color: var(--muted);
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 0;
        border-radius: 14px;
        padding: 10px 14px;
        min-width: 120px;
        width: auto;
        color: #041018;
        font-weight: 800;
        background: linear-gradient(135deg, var(--accent), var(--accent-2));
        cursor: pointer;
        transition: transform 0.18s ease, box-shadow 0.18s ease;
        box-shadow: 0 16px 36px rgba(73, 212, 157, 0.24);
    }

    .btn.full {
        width: 100%;
        min-width: 0;
        padding: 12px 16px;
    }

    .btn.small {
        padding: 8px 10px;
        min-width: 90px;
        font-weight: 700;
    }

    .btn.primary {
        background: linear-gradient(135deg, var(--accent), var(--accent-2));
        color: #041018;
    }

    .btn.secondary {
        background: linear-gradient(135deg, #6ec8ff, #49d49d);
        color: #041018;
    }

    .btn.danger {
        background: linear-gradient(90deg, #ff7d7d, #ffb3b3);
        color: #041018;
    }

    .btn:hover {
        transform: translateY(-1px);
    }

    .alerts {
        margin: 0 0 18px;
        display: grid;
        gap: 10px;
    }

    .alert {
        padding: 14px 16px;
        border-radius: 14px;
        border: 1px solid rgba(255, 255, 255, 0.08);
    }

    .alert.success {
        background: rgba(73, 212, 157, 0.12);
        color: #bff4dc;
    }

    .alert.error {
        background: rgba(255, 125, 125, 0.12);
        color: #ffd1d1;
    }

    .result-grid {
        display: grid;
        grid-template-columns: 0.9fr 1.1fr;
        gap: 24px;
        margin-top: 24px;
    }

    .result-card,
    .history-card {
        padding: 24px;
    }

    .section-head {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: baseline;
        margin-bottom: 18px;
    }

    .section-head h2 {
        margin: 0;
        font-size: 1.15rem;
    }

    .section-head span {
        color: var(--muted);
        font-size: 0.92rem;
    }

    .preview {
        width: 100%;
        aspect-ratio: 1 / 1;
        object-fit: cover;
        border-radius: 18px;
        border: 1px solid rgba(255, 255, 255, 0.08);
        background: rgba(255, 255, 255, 0.04);
    }

    .prediction {
        margin-top: 18px;
        padding: 18px;
        border-radius: 18px;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.08);
    }

    .prediction .label {
        display: block;
        font-size: 0.9rem;
        color: var(--muted);
        margin-bottom: 6px;
    }

    .prediction .value {
        font-size: 1.7rem;
        font-weight: 800;
        letter-spacing: -0.03em;
    }

    .chips {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 14px;
    }

    .chip {
        padding: 8px 12px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.07);
        color: var(--muted);
        font-size: 0.88rem;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th,
    td {
        padding: 12px 10px;
        text-align: left;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        vertical-align: top;
    }

    th {
        color: var(--muted);
        font-size: 0.86rem;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }

    .status {
        display: inline-flex;
        align-items: center;
        padding: 7px 10px;
        border-radius: 999px;
        font-size: 0.82rem;
        font-weight: 700;
    }

    .status.completed {
        background: rgba(73, 212, 157, 0.12);
        color: #bff4dc;
    }

    .status.processing {
        background: rgba(110, 200, 255, 0.12);
        color: #cceaff;
    }

    .status.failed {
        background: rgba(255, 125, 125, 0.12);
        color: #ffd1d1;
    }

    .empty {
        color: var(--muted);
        padding: 14px 0 4px;
    }

    @media (max-width: 980px) {

        .hero,
        .result-grid {
            grid-template-columns: 1fr;
        }

        .stats {
            grid-template-columns: 1fr;
        }

        .wrap {
            width: min(100% - 20px, 1180px);
        }
    }
    </style>
</head>

<body>
    <main class="wrap">
        <section class="hero">
            <div class="panel hero-copy">
                <div class="eyebrow">Computer Vision Waste Classifier</div>
                <h1>Upload ảnh và nhận kết quả phân loại ngay.</h1>
                <p class="lede">
                    Ảnh được lưu vào database cùng kết quả dự đoán. Backend sử dụng pipeline feature engineering từ
                    notebook của bạn,
                    nên khi thả đúng file model pickle vào thư mục <strong>storage/app/model</strong> thì web sẽ trả kết
                    quả trực tiếp.
                </p>

                <div class="stats">
                    <div class="stat">
                        <span class="value">128×128</span>
                        <span class="label">Kích thước ảnh đầu vào theo notebook</span>
                    </div>
                    <div class="stat">
                        <span class="value">129+</span>
                        <span class="label">Feature vector được tái tạo đúng pipeline</span>
                    </div>
                    <div class="stat">
                        <span class="value">DB + Storage</span>
                        <span class="label">Lưu ảnh, nhãn và payload dự đoán</span>
                    </div>
                </div>
            </div>

            <div class="panel form-panel">
                <h2 class="form-title">Tải ảnh lên</h2>
                <p class="form-note">Chọn 1 ảnh rác hoặc vật thể để hệ thống phân loại. Ảnh sẽ được lưu lại cho lịch sử
                    truy vấn.</p>

                <div class="alerts">
                    @if (session('success'))
                    <div class="alert success">{{ session('success') }}</div>
                    @endif

                    @if ($errors->any())
                    <div class="alert error">
                        {{ $errors->first() }}
                    </div>
                    @endif
                </div>

                <form action="{{ route('waste-classifications.store') }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="upload-box">
                        <input type="file" name="image" accept="image/*" required>
                    </div>
                    <div style="display:flex;gap:12px;align-items:center;">
                        <button type="submit" class="btn full primary">Phân loại ngay</button>
                        <button type="button" id="open-camera-btn" class="btn secondary" style="min-width:140px;">Mở
                            Camera</button>
                    </div>
                </form>
            </div>
        </section>

        <section class="result-grid">
            <div class="panel result-card">
                <div class="section-head">
                    <h2>Kết quả mới nhất</h2>
                    <div style="display:flex;align-items:center;gap:12px;">
                        <span>{{ $latestResult ? $latestResult->created_at->format('d/m/Y H:i') : 'Chưa có kết quả' }}</span>
                        @if ($latestResult)
                        <form action="{{ route('waste-classifications.destroy', $latestResult->id) }}" method="post"
                            onsubmit="return confirm('Xác nhận xóa bản ghi này?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn small danger">Xóa bản ghi</button>
                        </form>
                        @endif
                    </div>
                </div>

                @if ($latestResult)
                <img class="preview" src="{{ $latestResult->image_url }}" alt="Ảnh đã upload">

                <div class="prediction">
                    <span class="label">Trạng thái</span>
                    <span class="status {{ $latestResult->status }}">{{ strtoupper($latestResult->status) }}</span>

                    <div style="margin-top: 14px;">
                        <span class="label">Nhãn dự đoán</span>
                        <div class="value">
                            {{ $latestResult->predicted_label ?? 'Chưa có kết quả' }}
                        </div>
                    </div>

                    <div class="chips">
                        <span class="chip">File gốc: {{ $latestResult->original_name }}</span>
                        @if (! is_null($latestResult->confidence))
                        <span class="chip">Confidence: {{ number_format($latestResult->confidence * 100, 2) }}%</span>
                        @endif
                        @if ($latestResult->predicted_at)
                        <span class="chip">Dự đoán lúc: {{ $latestResult->predicted_at->format('H:i d/m/Y') }}</span>
                        @endif
                    </div>

                    @if ($latestResult->error_message)
                    <div class="alert error" style="margin-top: 14px;">
                        {{ $latestResult->error_message }}
                    </div>
                    @endif

                    @if ($latestResult->prediction_payload)
                    @php
                    $payload = $latestResult->prediction_payload;
                    $predLabel = $payload['label'] ?? null;
                    $predConfidence = $payload['confidence'] ?? null;
                    $probabilities = $payload['probabilities'] ?? [];
                    $modelFile = $payload['model_file'] ?? null;
                    $scalerFile = $payload['scaler_file'] ?? null;
                    $featureConfig = $payload['feature_config'] ?? null;
                    $classLabels = ['cardboard','glass','metal','paper','plastic'];
                    @endphp

                    <div style="margin-top:14px;">
                        <div style="font-size:0.95rem;color:var(--muted);margin-bottom:8px;">Chi tiết phân tích</div>

                        <div style="display:flex;gap:18px;align-items:center;flex-wrap:wrap;">
                            <div style="min-width:160px;">
                                <div style="color:var(--muted);font-size:0.86rem">Nhãn</div>
                                <div style="font-weight:800;font-size:1.1rem">{{ $predLabel ?? 'N/A' }}</div>
                            </div>

                            <div style="min-width:160px;">
                                <div style="color:var(--muted);font-size:0.86rem">Độ tin cậy</div>
                                <div style="font-weight:800;font-size:1.1rem">
                                    {{ $predConfidence ? number_format($predConfidence * 100, 2) . '%' : 'N/A' }}</div>
                            </div>

                            <div style="min-width:200px;">
                                <div style="color:var(--muted);font-size:0.86rem">Mô hình</div>
                                <div style="font-size:0.9rem">{{ $modelFile ?? '-' }} · {{ $scalerFile ?? '-' }}</div>
                            </div>
                        </div>

                        <div style="margin-top:12px">
                            <div style="color:var(--muted);font-size:0.9rem;margin-bottom:8px;">Xác suất theo lớp</div>
                            @foreach($probabilities as $i => $p)
                            @php
                            $labelName = $classLabels[$i] ?? ("class_".$i);
                            $pct = max(0, min(100, $p * 100));
                            @endphp
                            <div style="margin-bottom:8px">
                                <div
                                    style="display:flex;justify-content:space-between;font-size:0.86rem;color:var(--muted);margin-bottom:4px">
                                    <span>{{ ucfirst($labelName) }}</span><span>{{ number_format($p * 100, 2) }}%</span>
                                </div>
                                <div
                                    style="background:rgba(255,255,255,0.06);height:10px;border-radius:6px;overflow:hidden;">
                                    <div class="prob-bar" data-pct="{{ $pct }}"
                                        style="height:10px;background:linear-gradient(90deg,var(--accent),var(--accent-2));">
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        @if ($featureConfig)
                        <div style="margin-top:12px;color:var(--muted);font-size:0.86rem">
                            Feature config: HSV bins = {{ $featureConfig['hsv_bins'] ?? 'N/A' }}, HOG =
                            {{ $featureConfig['use_hog'] ? 'yes' : 'no' }}, GLCM =
                            {{ $featureConfig['use_glcm'] ? 'yes' : 'no' }}
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
                @else
                <div class="empty">Hãy upload một ảnh để xem kết quả phân loại ở đây.</div>
                @endif
            </div>

            <div class="panel history-card">
                <div class="section-head">
                    <h2>Lịch sử gần đây</h2>
                    <span>{{ $recentScans->count() }} bản ghi</span>
                </div>

                @if ($recentScans->isEmpty())
                <div class="empty">Chưa có ảnh nào được lưu trong database.</div>
                @else
                <table>
                    <thead>
                        <tr>
                            <th>Ảnh</th>
                            <th>Kết quả</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentScans as $scan)
                        <tr>
                            <td>
                                <div style="display:flex; gap:12px; align-items:center;">
                                    <img src="{{ $scan->image_url }}" alt="{{ $scan->original_name }}"
                                        style="width:54px;height:54px;border-radius:14px;object-fit:cover;border:1px solid rgba(255,255,255,.08);">
                                    <div>
                                        <div style="font-weight:700;">{{ $scan->original_name }}</div>
                                        <div style="color:var(--muted);font-size:.86rem;">
                                            {{ $scan->created_at->format('d/m H:i') }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                {{ $scan->predicted_label ?? 'Chưa có' }}
                                @if (! is_null($scan->confidence))
                                <div style="color:var(--muted);font-size:.86rem;">
                                    {{ number_format($scan->confidence * 100, 1) }}%</div>
                                @endif
                            </td>
                            <td style="display:flex;gap:8px;align-items:center;">
                                <span class="status {{ $scan->status }}">{{ strtoupper($scan->status) }}</span>
                                <form action="{{ route('waste-classifications.destroy', $scan->id) }}" method="post"
                                    onsubmit="return confirm('Xác nhận xóa bản ghi này?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn small danger">Xóa bản ghi</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>
        </section>
    </main>
    <!-- Camera modal -->
    <div id="camera-modal"
        style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.6);align-items:center;justify-content:center;z-index:9999;">
        <div style="background:#0b1a27;padding:16px;border-radius:12px;min-width:300px;max-width:800px;">
            <div
                style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;color:var(--muted)">
                <strong>Camera</strong>
                <button id="close-camera-btn"
                    style="background:transparent;border:0;color:var(--muted);font-weight:700;">Đóng</button>
            </div>
            <video id="camera-video" autoplay playsinline
                style="width:100%;height:auto;border-radius:8px;background:#000;">Trình duyệt không hỗ trợ
                camera</video>
            <div style="display:flex;gap:8px;margin-top:8px;justify-content:center;">
                <button id="capture-btn" class="btn primary" style="min-width:140px;">Chụp và gửi</button>
            </div>
        </div>
    </div>
    <script>
    (function() {
        console.log('[waste-classifier] camera script loaded');
        const openBtn = document.getElementById('open-camera-btn');
        const modal = document.getElementById('camera-modal');
        const video = document.getElementById('camera-video');
        const captureBtn = document.getElementById('capture-btn');
        const closeBtn = document.getElementById('close-camera-btn');
        let stream = null;

        function stopStream() {
            if (stream) {
                stream.getTracks().forEach(t => t.stop());
                stream = null;
            }
        }

        openBtn && openBtn.addEventListener('click', async function() {
            console.log('[waste-classifier] open camera button clicked');
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                alert('Trình duyệt không hỗ trợ getUserMedia.');
                console.error('[waste-classifier] navigator.mediaDevices not available');
                return;
            }
            try {
                stream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: 'environment'
                    },
                    audio: false
                });
                video.srcObject = stream;
                // try to play (some browsers require explicit play())
                try {
                    await video.play();
                } catch (e) {
                    console.warn('[waste-classifier] video.play() failed', e);
                }
                modal.style.display = 'flex';
                console.log('[waste-classifier] camera stream started');
            } catch (err) {
                alert('Không thể truy cập camera: ' + (err.message || err));
                console.error('[waste-classifier] getUserMedia error', err);
            }
        });

        closeBtn && closeBtn.addEventListener('click', function() {
            console.log('[waste-classifier] close camera');
            stopStream();
            modal.style.display = 'none';
        });

        captureBtn && captureBtn.addEventListener('click', function() {
            console.log('[waste-classifier] capture button clicked');
            if (!video || !video.videoWidth) {
                alert('Camera chưa sẵn sàng. Hãy đợi video load hoặc cho phép quyền truy cập.');
                console.warn('[waste-classifier] video not ready', {
                    video
                });
                return;
            }
            const canvas = document.createElement('canvas');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

            // Fallback if toBlob is not supported
            if (!canvas.toBlob) {
                try {
                    const dataUrl = canvas.toDataURL('image/jpeg', 0.9);
                    const byteString = atob(dataUrl.split(',')[1]);
                    const ab = new ArrayBuffer(byteString.length);
                    const ia = new Uint8Array(ab);
                    for (let i = 0; i < byteString.length; i++) ia[i] = byteString.charCodeAt(i);
                    const blob = new Blob([ab], {
                        type: 'image/jpeg'
                    });
                    sendBlob(blob);
                } catch (e) {
                    alert('Không thể tạo ảnh: ' + (e.message || e));
                    console.error('[waste-classifier] toDataURL fallback failed', e);
                }
                return;
            }

            canvas.toBlob(function(blob) {
                if (!blob) {
                    alert('Không thể tạo ảnh.');
                    console.error('[waste-classifier] toBlob returned null');
                    return;
                }
                sendBlob(blob);
            }, 'image/jpeg', 0.9);
        });

        async function sendBlob(blob) {
            console.log('[waste-classifier] sending blob', blob);
            const tokenEl = document.querySelector('input[name="_token"]');
            const token = tokenEl ? tokenEl.value : '';
            const fd = new FormData();
            fd.append('_token', token);
            fd.append('image', blob, 'camera.jpg');

            try {
                const res = await fetch("{{ route('waste-classifications.store') }}", {
                    method: 'POST',
                    body: fd,
                    credentials: 'same-origin'
                });
                console.log('[waste-classifier] fetch response', res);
                if (res.redirected) {
                    window.location.href = res.url;
                    return;
                }
                // try to parse JSON or fallback to reload
                try {
                    const j = await res.json();
                    console.log('[waste-classifier] response json', j);
                } catch (e) {
                    console.warn('[waste-classifier] response not json, reloading');
                }
                window.location.reload();
            } catch (e) {
                alert('Gửi ảnh thất bại: ' + (e.message || e));
                console.error('[waste-classifier] send error', e);
            } finally {
                stopStream();
                modal.style.display = 'none';
            }
        }
    })();
    </script>

    <script>
    // Set widths for probability bars from data attributes (avoid blade in style attr)
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.prob-bar').forEach(function(el) {
            const pct = el.getAttribute('data-pct');
            if (pct !== null) {
                el.style.width = pct + '%';
            }
        });
    });
    </script>

</body>

</html>