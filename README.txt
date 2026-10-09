EDUFIND ADMISSION - FIXED SETUP

1. Copy this folder to C:\xampp\htdocs\
2. Open XAMPP.
3. Start Apache.
4. Start MySQL. It must show Running.
5. Open http://localhost/phpmyadmin/
6. Import database.sql. This creates the edufind_admission database and applications table.
7. Open http://localhost/edufind_admission_fixed/

IMPORTANT:
The connection file tries MySQL ports 3306 and 3307 automatically.
If MySQL is not Running in XAMPP, form submission cannot save data.

For uploaded files, submit.php creates upload/academic, upload/cnic and upload/photos automatically.
