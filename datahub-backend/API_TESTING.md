# API Testing Guide - Polban DataHub

## Base URL
```
http://127.0.0.1:8000/api
```

## Authentication
Semua endpoint (kecuali `/login`) memerlukan Bearer Token di header:
```
Authorization: Bearer {your_token}
```

---

## 1. LOGIN (Public)

**POST** `/api/login`

### Request Body:
```json
{
  "email": "admin@polban.ac.id",
  "password": "password"
}
```

### Response Success (200):
```json
{
  "message": "Login successful",
  "user": {
    "id": 1,
    "name": "Admin",
    "email": "admin@polban.ac.id",
    "role": "admin"
  },
  "token": "1|xxxxxxxxxxxxxxxxxxxxx"
}
```

### Testing dengan curl (PowerShell):
```powershell
$body = @{
    email = "admin@polban.ac.id"
    password = "password"
} | ConvertTo-Json

Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/login" -Method POST -Body $body -ContentType "application/json"
```

---

## 2. GET USER INFO (Protected)

**GET** `/api/user`

### Headers:
```
Authorization: Bearer {token}
```

---

## 3. IMPORT DATA (Participant Only)

**POST** `/api/import-data`

### Headers:
```
Authorization: Bearer {participant_token}
Content-Type: multipart/form-data
```

### Request Body:
```
file: [CSV/Excel file]
```

### CSV Format Example:
```csv
kelas,angkatan,tgl_lahir,jenis_kelamin,agama,kode_pos,nama_slta_raw,nama_jalur_daftar_raw,nama_wilayah_raw,provinsi_raw
1A,2024,2006-01-15,L,Islam,40132,SMAN 1 Bandung,SNBP,Kota Bandung,Jawa Barat
2B,2024,2006-03-20,P,Kristen,40215,SMAN 2 Bandung,SNBT,Kabupaten Bandung,Jawa Barat
```

---

## 4. GET PENDING IMPORTS (Admin Only)

**GET** `/api/admin/pending-imports`

### Headers:
```
Authorization: Bearer {admin_token}
```

---

## 5. GET IMPORT DETAIL (Admin Only)

**GET** `/api/admin/pending-imports/{id}`

---

## 6. APPROVE IMPORT (Admin Only)

**POST** `/api/admin/approve/{id}`

### Headers:
```
Authorization: Bearer {admin_token}
```

---

## 7. REJECT IMPORT (Admin Only)

**POST** `/api/admin/reject/{id}`

### Request Body:
```json
{
  "notes": "Data tidak lengkap atau tidak sesuai"
}
```

---

## 8. GET ACTIVITY LOGS (Admin Only)

**GET** `/api/admin/activity-logs`

### Query Parameters (Optional):
- `action` - Filter by action type (login, import_data, approve_data, etc)
- `user_id` - Filter by user ID
- `per_page` - Records per page (default: 50)

---

## 9. EXPORT DATA (Admin & Participant)

**GET** `/api/export-data`

### Query Parameters (Optional):
- `angkatan` - Filter by angkatan
- `kelas` - Filter by kelas
- `tahun` - Filter by year

---

## 10. LOGOUT (Protected)

**POST** `/api/logout`

### Headers:
```
Authorization: Bearer {token}
```

---

## Testing Accounts

### Admin Account:
- Email: `admin@polban.ac.id`
- Password: `password`
- Role: `admin`

### Participant Account:
- Email: `participant@polban.ac.id`
- Password: `password`
- Role: `participant`
