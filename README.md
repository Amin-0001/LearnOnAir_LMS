# Learn OnAir - Comprehensive LMS Platform

**Learn OnAir** is a robust, full-stack Learning Management System (LMS) developed as a final year BCA project for Presidency Autonomous College. This platform facilitates structured e-learning by bringing students, instructors, and administrators onto a single, cohesive, modern Enterprise UI platform.

---

## 🎯 Features & Core Modules

The platform operates on a secure three-tier architectural model:

### 1. Student Portal 🎓
* **Course Discovery & Checkout**: Browse an interactive masterclass directory and natively enroll using a mock encrypted checkout gateway simulation (UPI/Card).
* **Interactive Player Environment**: Native video player modules integrated directly alongside instructor-provided PDF study materials.
* **Assessment & Certification**: Includes a dynamic multiple-choice testing engine. Automatically generates customized, high-resolution HTML certificates for students upon clearing a 50% passing threshold.
* **E-Library Store**: Access an advanced digital repository equipped with live JavaScript-driven filtering and search mechanics.

### 2. Instructor Portal 👨‍🏫
* **Course Sandbox**: Allows absolute creation and structural management of video lessons, supplemental PDFs, and modular quizzes.
* **Smart Tracking**: Instantly view and evaluate localized 'Student Assignments' utilizing dedicated grading submission forms.
* **Live Communications**: Global notification dispatches mapping directly to SQL triggers instantly alert students of new study materials.

### 3. Administrator Console 🛡️
* **Platform Governance**: Manages macro course verification standards allowing admins to accept or reject instructor-published shells, preserving high UI/UX platform quality.
* **User Matrix**: Seamlessly moderate role hierarchies across thousands of logged users (elevating users to 'Teachers' safely).

---

## 💻 Tech Stack & Aesthetic Standard
* **Backend Engine**: Native `PHP 8` / `MySQLi`
* **Frontend Layer**: `HTML5`, `Vanilla JavaScript`, `CSS3` Grid/Flex Layouts.
* **Aesthetic Standard**: Engineered utilizing an exclusively flat Enterprise UI design:
  * Strict `Cream-White` Surface Backgrounds.
  * Sharp `Slate Black` Typography.
  * Deep `Primary Light Blue` pill branding & call-to-actions.
* **Security Assets**: Includes legacy-password automated upgrading schemas executing securely through `password_verify` and `password_hash` hooks natively during authentication.

---

## 🚀 Installation & Local Setup

To deploy **Learn OnAir** on a local testing environment, you will require an Apache deployment (such as **XAMPP** or **WAMP**).

1. Clone the repository into your standard local webserver root directory (`htdocs` for XAMPP):
   ```bash
   git clone https://github.com/Amin-0001/LearnOnAir_LMS.git
   ```
2. Spin up **Apache** and **MySQL** instances through the XAMPP Control Panel.
3. Access `localhost/phpmyadmin` to generate a target database named `lms_db`.
4. Import the included database `.sql` schemas.
5. Open `localhost/LearnOnAir_LMS` physically within your browser to engage the system!

---

*This application was engineered structurally, logically, and aesthetically to mimic real-world enterprise architectures for professional learning environments.*
