# 🚀 PeerLink - P2P Micro-Lending Platform

**PeerLink** is a digital micro-lending platform that connects individuals who need small loans with others willing to lend.  
It brings **transparency**, **trust**, and **automation** to peer lending by using **PayHero** for instant payments, introducing **digital collateral**, **tracking repayments**, and building a **user reputation system**.

This project is built with the **Laravel framework** and demonstrates a complete, end-to-end lending cycle with real-world payment integrations, role-based access, and automated backend processes.

---

## ✨ Core Features

### 👤 Borrower Features
- Register as a Borrower.
- Profile Management with avatar uploads to **Amazon S3**.
- Create Loan Requests with a system-set interest rate.
- **Wallet Management:** Deposit funds (via real **PayHero STK Push**) and request withdrawals.
- **Automatic Collateral Locking:** A percentage of the loan amount is locked in the wallet before the request goes live.
- **Repay Loan:** Repay instantly from wallet balance or via STK Push.
- **Reputation Score:** Score increases with successful repayments.

---

### 💰 Lender Features
- Register as a Lender.
- **Browse Approved Loan Requests:** View a marketplace of active loan requests from borrowers.
- **Fund Loans:** Instantly transfer funds from your wallet to a borrower to activate a loan.
- **Track Investments:** Dashboard showing funded loans, their status (active/repaid), and total profit earned.
- **Wallet Management:** Deposit and withdraw funds.

---

### 🛠️ Platform (Admin) Features
- **Rich Dashboard:** View key stats — total users, total money lent, daily loan charts, user list, and transaction feed.
- **Loan Approval System:** Approve or reject new loan requests.
- **Automated Processes:** The system automatically handles overdue loan penalties and transaction timeouts.

---

## 🧩 System Architecture & Logic

**PeerLink** is built on a modern, asynchronous architecture for speed and reliability.

| Component | Technology |
|------------|-------------|
| Frontend | Laravel Blade + Tailwind CSS + Alpine.js |
| Backend | Laravel 11 (PHP 8.2+) |
| Database | MySQL (Hosted on Railway.app) |
| Payments | PayHero Kenya (STK Push & Payouts) |
| File Storage | Amazon S3 |
| Queues | Laravel Database Queue Driver |

---

## 💳 Payment Flow (Asynchronous)

1. **Initiation:**  
   A user action (deposit, repay) creates a pending transaction in the database and dispatches a Job (e.g., `InitiatePayHeroPayment`) to the queue.
2. **Immediate Response:**  
   The user sees `"Your request is being processed..."`.
3. **Queue Worker:**  
   A background `queue:work` process makes the actual API call to **PayHero** via the `PayHeroService`.
4. **Confirmation:**  
   PayHero sends a webhook to a public URL (managed via **Ngrok** in development) confirming whether the payment was successful or failed.
5. **Webhook Handler:**  
   The `PayHeroWebhookController` receives the notification, verifies it, and updates transaction status, wallet balances, and loan statuses accordingly.

---

## 🧰 Local Development Setup

### ✅ Prerequisites
- PHP **8.2+**
- Composer
- Node.js & npm
- Railway.app account (for MySQL database)
- PayHero Kenya merchant account
- AWS S3 bucket with IAM credentials
- Ngrok (for local webhook testing)

---

### ⚙️ Installation Steps

```bash
# Clone the repository
git clone https://github.com/SingasonSimon/peerlink.git
cd peerlink

# Install dependencies
composer install
npm install
