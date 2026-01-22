# Kan-San Admin - Thai Lottery Management System

Laravel 12 admin panel for managing Thai lottery ticket sales and results.

---

## 🎯 Secondary Sales Transaction System

### Key Features

#### 1. **Multi-Ticket Batch Purchases**
- Select **multiple tickets** for the same customer in one transaction
- Hold `Ctrl` (or `Cmd` on Mac) to multi-select tickets
- Amount is automatically split evenly across all tickets

#### 2. **Smart Batch Linking**
**Logic:** Same Customer + Same Draw Date = Same Batch Link

**Example:**
```
Customer: Ko Myo Ko
- Jan 1: Buys ticket 123456 for Feb 1 draw → Batch Link: ABC123
- Jan 5: Buys ticket 789012 for Feb 1 draw → Same Link: ABC123 ✅
- Jan 10: Buys ticket 345678 for Feb 1 draw → Same Link: ABC123 ✅
- Jan 15: Buys ticket 111222 for Feb 16 draw → New Link: XYZ789 (different draw)
```

**Benefits:**
- Customer gets **one link per draw date** to check all their tickets
- Easier tracking and management
- Better customer experience

#### 3. **Customer Management**
Two ways to add customers:

**Option A: Select Existing Customer**
- Dropdown with searchable customer list
- Auto-fills customer name and phone

**Option B: Manual Entry (New Customer)**
- Enter name and/or phone number
- System creates account with default password: `123456`
- Either field is acceptable (name OR phone)

**Customer Matching:**
- Searches by phone number first
- If found, updates customer info
- If not found, creates new account

#### 4. **Token System**
Each transaction has two tokens:

- **`public_token`**: Unique per ticket (individual check)
- **`batch_token`**: Shared across customer + draw date (batch check)

**Routes:**
```php
// Individual ticket check
/lottery-result/{public_token}

// Batch check (all tickets for customer + draw)
/customer-batch/{batch_token}
```

---

## 🔧 Technical Implementation

### Database Schema
```sql
secondary_sales_transactions:
  - customer_id (links to customers table)
  - customer_name (stored for display)
  - customer_phone (stored for display)
  - secondary_ticket_id (links to ticket)
  - public_token (unique per transaction)
  - batch_token (shared for customer + draw date)
  - amount_thb, amount_mmk (split evenly if multiple tickets)
```

### Batch Token Logic
```php
// Find existing batch for same customer + draw date
$existingTransaction = SecondarySalesTransaction::where('customer_id', $customerId)
    ->whereHas('secondaryTicket', function($q) use ($drawDate) {
        $q->whereDate('withdraw_date', $drawDate);
    })
    ->whereNotNull('batch_token')
    ->first();

// Reuse existing or generate new
$batchToken = $existingTransaction?->batch_token ?? Str::random(32);
```

---

## 📋 Recent Bug Fixes

### ✅ Fixed Issues
1. **Password Field Error** - Added default password `123456` for new customers
2. **Customer Update Logic** - Search by phone first, then update
3. **Validation** - Require either customer name OR phone (not both mandatory)
4. **Duplicate Transaction Numbers** - Changed from `count()` to `max()` for ID generation
5. **Customer Dropdown Selection** - Added `customer_id` to validation
6. **Multi-Ticket Batch** - Restored batch linking functionality
7. **Smart Batch Reuse** - Same customer + draw date = same batch link

### ✅ Error Page Redesign
- Modern dark theme with gradient effects
- Fully responsive (mobile to 4K)
- SVG icons with animations
- Glassmorphism effects

---

## 🚀 Remaining Features to Implement

### High Priority
- [ ] **Telescope Monitoring** - Replace cPanel with Laravel Telescope
- [ ] **Database Indexes Migration** - Run performance indexes migration
- [ ] **Cache Configuration** - Set up Redis for draw results caching
- [ ] **Historical Ticket UI** - Add UI for manually checking old tickets

### Medium Priority
- [ ] **Batch Edit** - Edit multiple transactions at once
- [ ] **Customer Portal** - Allow customers to view their tickets
- [ ] **SMS Notifications** - Send batch link via SMS
- [ ] **Export Reports** - CSV/PDF export for transactions

### Low Priority
- [ ] **Multi-language Support** - Thai/English toggle
- [ ] **Dark Mode Toggle** - User preference for admin panel
- [ ] **Advanced Filters** - More filtering options on transaction list

---

## 🛠️ Development Commands

### Cron Setup
```bash
crontab -e
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

### Manual Testing
```bash
# Test daily quotes
php artisan quotes:send-daily

# Test scheduled announcements
php artisan announcements:send-scheduled

# Clear all caches
php artisan optimize:clear
php artisan view:clear
php artisan cache:clear
```

### Database
```bash
# Run migrations
php artisan migrate

# Run performance indexes (when ready)
php artisan migrate --path=/database/migrations/2026_01_21_000001_add_lottery_performance_indexes.php
```

---

## 📝 Notes

### Default Credentials
- **New Customer Password**: `123456`
- Customers should change this on first login

### Important Files
- **Controller**: `app/Http/Controllers/SecondarySalesController.php`
- **Service**: `app/Services/LotteryResultCheckerService.php`
- **Model**: `app/Models/SecondarySalesTransaction.php`
- **View**: `resources/views/secondary-sales/transactions/create.blade.php`

### Batch Token Behavior
- **Reused**: Same customer + same draw date
- **New**: Different customer OR different draw date
- **Permanent**: Never expires, always accessible

---

## 🎨 UI Features

### Transaction Creation Form
- Multi-select ticket dropdown with Choices.js
- Customer search with autocomplete
- Real-time amount calculation
- Payment method selection
- Batch link preview on success

### Success Message Format
```
✅ 3 ticket(s) sold to Ko Myo Ko! (Added to existing batch)
Tickets: 123456, 789012, 345678

📱 Batch Link: https://example.com/customer-batch/ABC123
```

---

**Last Updated**: January 22, 2026
