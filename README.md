# Knuckleball

https://knuckleball.app is a "through the mail" website that allows users to view and submit addresses for their sports heroes. This allows them to send items to get signed (for example balls or shirts)

---

## 🧰 Tech Stack
- **Language:** e.g., PHP / JavaScript
- **Framework:** e.g., Laravel / React / Django
- **Database:** e.g., MySQL
- **Other Tools:** e.g., TailwindCSS

---

## 📦 Installation

You need php and node installed to develop locally.

```bash
# Clone the repository
git clone https://github.com/allenjd3/knuckleball-f.git

# Move into the project directory
cd knuckleball-f

# Copy environment variables
cp .env.example .env

# Install dependencies
composer install
npm install && npm run build

# Run build commands
php artisan key:generate
php artisan storage:link

# Start your dev environment
composer run dev