# 🚀 Quick Start Guide - QR Code Validation System

## ⚡ Getting Started in 5 Minutes

### Step 1: Verify Application is Running
```bash
# Check Docker containers
docker compose -f /root/gawe/checksheet/docker-compose.yml ps

# You should see 3 running containers:
# - sample (PHP-FPM)
# - db_sample (MariaDB)
# - nginx_sample (Nginx)
```

### Step 2: Login to Admin Panel
- Open browser: **http://localhost/admin/login**
- Email: `admin@admin.com`
- Password: `password`

### Step 3: View Your Dashboard
- Go to: **http://localhost/admin/** (after login)
- See patrol monitoring with QR validation status
- Calendar grid shows:
  - 🟢 Green = QR validated
  - 🔴 Red = Not validated yet
  - — Dash = Not assigned

### Step 4: Scan QR Code (or Test Manually)
- Go to: **http://localhost/admin/patrols/scan-qr**
- Either:
  - Paste a token from test data
  - Scan actual QR code
- System will show patrol details and validation status

### Step 5: View Patrol Records
- Go to: **http://localhost/admin/patrols**
- See all patrols with QR scan status
- "QR Scan" column shows validation status

---

## 🎯 Key Features At-a-Glance

| Feature | Location | How to Use |
|---------|----------|-----------|
| **Dashboard** | `/admin/` | Month/year filter, see QR validation % |
| **QR Scan** | `/admin/patrols/scan-qr` | Input token or scan QR code |
| **Patrol Records** | `/admin/patrols` | View all patrols with QR status |
| **Employees** | `/admin/employees` | Link user accounts to employees |
| **Locations** | `/admin/locations` | Manage patrol checkpoint locations |

---

## 📊 Understanding the Dashboard

### Stats Cards (Top)
- **Validasi QR Code:** How many users have completed QR scans
- **Total User:** Total number of patrol personnel
- **Titik Patrol:** Number of locations (checkpoints)
- **Kelengkapan:** Average location completion percentage

### Calendar Grid (Main)
- **Rows:** User + Location combinations
- **Columns:** Days of selected month
- **Colors:**
  - Green ✓ = Patrol completed & QR scanned
  - Red ✗ = Patrol assigned but no QR scan
  - Gray — = Shift not assigned that day

### Detail Panel (Bottom)
- Shows per-user completion progress
- Completion % for each location
- Lists active patrol days

---

## 🔧 Common Tasks

### 1. View My Patrols
```
1. Login as user
2. Go to Dashboard (/admin/)
3. See your patrol records only
4. Filter by month/year
```

### 2. Validate a Patrol with QR
```
1. Go to /admin/patrols/scan-qr
2. Input or scan QR token
3. System validates and shows details
4. Check Dashboard to see updated status
```

### 3. Add New Location
```
1. Go to /admin/locations
2. Click "Create"
3. Enter:
   - Location name
   - GPS coordinates (optional)
   - Radius (optional)
4. Save
```

### 4. Create Employee
```
1. Go to /admin/employees
2. Click "Create"
3. Enter:
   - NIP (employee ID)
   - Name
   - Shift Group (A/B/C/D)
   - Link User Account
4. Save
```

---

## 📱 Mobile Access

The dashboard is **fully responsive** on mobile/tablet:
- Tap stats cards to see details
- Swipe through calendar
- All buttons optimized for touch
- Works on portrait and landscape

---

## 🐛 Troubleshooting

### Dashboard Shows No Data
**Solution:** Make sure you have patrol records
- Run: `docker compose exec -T sample php artisan db:seed --class=PatrolQrTestSeeder`
- This creates 69 test patrols

### Can't Login
**Solution:** 
- Verify email: `admin@admin.com`
- Password: `password`
- Check Docker containers are running

### QR Scan Shows "Token Not Found"
**Solution:**
- Token must exist in database
- Use test data or create new patrol first
- Paste exact token (case-sensitive)

### Database Seems Empty
**Solution:** Run seeders to populate test data
```bash
docker compose exec -T sample php artisan db:seed --class=LocationSeeder
docker compose exec -T sample php artisan db:seed --class=ShiftSeeder
docker compose exec -T sample php artisan db:seed --class=PatrolQrTestSeeder
```

---

## 📚 Full Documentation

For complete details, see:
- **`QR_CODE_SYSTEM.md`** - Complete technical documentation
- **`IMPLEMENTATION_COMPLETE.md`** - Implementation summary

---

## ✅ Verification Checklist

After setup, verify:
- [ ] Can access http://localhost/admin/login
- [ ] Can login with admin@admin.com / password
- [ ] Dashboard loads with patrol data
- [ ] Can see QR scan page
- [ ] Test data exists (69 patrols)
- [ ] Dashboard shows stats cards
- [ ] Can filter by month/year

---

## 🔗 Useful Links

**Local Access:**
- Admin Login: http://localhost/admin/login
- Dashboard: http://localhost/admin/
- Patrols: http://localhost/admin/patrols
- QR Scan: http://localhost/admin/patrols/scan-qr

**API (with Authentication):**
- Generate Token: POST `/api/qr/generate-token`
- Validate Scan: POST `/api/qr/validate/{token}`

**Docker Commands:**
```bash
# Start containers
docker compose -f /root/gawe/checksheet/docker-compose.yml up -d

# Stop containers
docker compose -f /root/gawe/checksheet/docker-compose.yml down

# View logs
docker compose -f /root/gawe/checksheet/docker-compose.yml logs -f sample

# Run artisan commands
docker compose exec -T sample php artisan <command>
```

---

## 💡 Tips & Tricks

1. **Keyboard Shortcut:** Press `Cmd+K` (Mac) or `Ctrl+K` (Windows) in Filament to open command palette

2. **Filter Patrols:** Use the "Group Shift" filter to see patrols by A/B/C/D groups

3. **Export Data:** Use Filament's built-in export (if configured) on patrols table

4. **Mobile QR Scanner:** Use any smartphone QR app - it just needs to extract the token

5. **Test QR Token:** All test patrol records have `qr_code_token` that can be manually entered

---

## 🎓 User Roles

Currently only **Admin** role is configured:
- Email: `admin@admin.com`
- Password: `password`
- Can see all patrols and users

**To add more users:**
1. Go to `/admin/users` (if available)
2. Create new user
3. Assign role from dropdown
4. Link to employee record

---

## ⏱️ Expected Load Times

- Dashboard: ~1-2 seconds
- Patrol Records: ~1 second
- QR Scan Page: ~500ms
- API Validation: ~100ms

If slower, check Docker container performance:
```bash
docker stats
```

---

## 🔄 Database Backup

To backup patrol data:
```bash
# Backup database
docker compose exec -T db_sample mysqldump -u root -pp455w0rd checksheet > backup.sql

# Restore database
docker compose exec -T db_sample mysql -u root -pp455w0rd checksheet < backup.sql
```

---

## 🎯 Success Indicators

You'll know the system is working when:
- ✅ Dashboard displays "Validasi QR Code: X dari Y"
- ✅ Calendar grid shows colored cells (green/red/gray)
- ✅ QR Scan page accepts tokens
- ✅ Patrol records show QR scan timestamps
- ✅ Stats calculate correct percentages
- ✅ Mobile view is responsive

---

## 📞 Need Help?

1. Check `QR_CODE_SYSTEM.md` for technical details
2. Review `IMPLEMENTATION_COMPLETE.md` for what was built
3. Check Docker logs: `docker compose logs -f sample`
4. Verify database: Run test seeders again
5. Clear cache: `docker compose exec -T sample php artisan cache:clear`

---

**System Status:** 🟢 **READY TO USE**

Enjoy your patrol monitoring system! 🚀
