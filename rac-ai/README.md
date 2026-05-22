# Waste Classifier Web App

Web Laravel để upload ảnh, lưu vào database, và trả kết quả phân loại bằng pipeline từ notebook.

## Chạy thử

- Cài dependency PHP và chạy migration:

```bash
composer install
php artisan migrate
php artisan storage:link
```

- Cài dependency Python:

```bash
pip install numpy opencv-python scikit-image scikit-learn
```

- Thả file model vào `storage/app/model`:

- `best_ml_model.pkl` hoặc `svm_model.pkl`
- `best_ml_scaler.pkl` hoặc `scaler.pkl`
- `label_encoder.pkl`

- Chạy ứng dụng:

```bash
php artisan serve
```

Nếu cần, chạy thêm frontend build:

```bash
npm install
npm run dev
```
