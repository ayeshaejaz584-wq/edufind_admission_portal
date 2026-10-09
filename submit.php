<?php
declare(strict_types=1);

header("Content-Type: text/plain; charset=UTF-8");

require_once __DIR__ . "/config.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    exit("Invalid request method.");
}

function postValue(string $key): string {
    return trim((string)($_POST[$key] ?? ""));
}

$application_id = postValue("application_id");
$first_name = postValue("first_name");
$last_name = postValue("last_name");
$cnic = postValue("cnic");
$dob = postValue("dob");
$email = postValue("email");
$phone = postValue("phone");
$gender = postValue("gender");
$city = postValue("city");
$address = postValue("address");

$qualification = postValue("qualification");
$board = postValue("board");
$roll_number = postValue("roll_number");
$passing_year = filter_var($_POST["passing_year"] ?? null, FILTER_VALIDATE_INT);
$total_marks = filter_var($_POST["total_marks"] ?? null, FILTER_VALIDATE_INT);
$obtained_marks = filter_var($_POST["obtained_marks"] ?? null, FILTER_VALIDATE_INT);

$university = postValue("university");
$campus = postValue("campus");
$program = postValue("program");
$session = postValue("session");

$required = [
    "First name" => $first_name,
    "Last name" => $last_name,
    "CNIC / B-Form" => $cnic,
    "Date of birth" => $dob,
    "Email" => $email,
    "Phone" => $phone,
    "Gender" => $gender,
    "City" => $city,
    "Address" => $address,
    "Qualification" => $qualification,
    "Board" => $board,
    "Passing year" => $passing_year,
    "Total marks" => $total_marks,
    "Obtained marks" => $obtained_marks,
    "University" => $university,
    "Campus" => $campus,
    "Program" => $program
];

foreach ($required as $label => $value) {
    if ($value === "" || $value === false || $value === null) {
        exit("Please fill the required field: " . $label);
    }
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    exit("Please enter a valid email address.");
}

$currentYear = (int)date("Y");
if ($passing_year < 1950 || $passing_year > $currentYear + 1) {
    exit("Please enter a valid passing year.");
}

if ($total_marks <= 0) {
    exit("Total marks must be greater than 0.");
}

if ($obtained_marks < 0 || $obtained_marks > $total_marks) {
    exit("Obtained marks are invalid.");
}

$percentage = round(($obtained_marks / $total_marks) * 100, 2);

if ($application_id === "") {
    $application_id = "EDF-" . date("Y") . "-" . random_int(100000, 999999);
}

/*
 * Save uploads inside this project:
 * admission/upload/academic/
 * admission/upload/cnic/
 * admission/upload/photos/
 */
$baseFolder = __DIR__ . "/upload/";
$academicFolder = $baseFolder . "academic/";
$cnicFolder = $baseFolder . "cnic/";
$photoFolder = $baseFolder . "photos/";

foreach ([$academicFolder, $cnicFolder, $photoFolder] as $folder) {
    if (!is_dir($folder) && !mkdir($folder, 0775, true)) {
        exit("Could not create upload folders.");
    }
}

function uploadDocument(
    ?array $file,
    string $folder,
    array $allowedExtensions,
    bool $required = true
) {
    if (!$file || !isset($file["error"])) {
        return $required ? false : "";
    }

    if ($file["error"] === UPLOAD_ERR_NO_FILE) {
        return $required ? false : "";
    }

    if ($file["error"] !== UPLOAD_ERR_OK) {
        return false;
    }

    if (($file["size"] ?? 0) > 5 * 1024 * 1024) {
        return false;
    }

    $extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));

    if (!in_array($extension, $allowedExtensions, true)) {
        return false;
    }

    // Check actual MIME type instead of trusting only the file extension.
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file["tmp_name"]);

    $allowedMimes = [
        "pdf"  => "application/pdf",
        "jpg"  => "image/jpeg",
        "jpeg" => "image/jpeg",
        "png"  => "image/png"
    ];

    if (!isset($allowedMimes[$extension]) || $mime !== $allowedMimes[$extension]) {
        return false;
    }

    $newFileName = bin2hex(random_bytes(16)) . "." . $extension;
    $destination = $folder . $newFileName;

    if (!move_uploaded_file($file["tmp_name"], $destination)) {
        return false;
    }

    // Store a project-relative path in MySQL.
    return "upload/" . basename($folder) . "/" . $newFileName;
}

$academic_document = uploadDocument(
    $_FILES["academicFile"] ?? null,
    $academicFolder,
    ["pdf", "jpg", "jpeg", "png"],
    true
);

$cnic_document = uploadDocument(
    $_FILES["cnicFile"] ?? null,
    $cnicFolder,
    ["pdf", "jpg", "jpeg", "png"],
    true
);

$photo_document = uploadDocument(
    $_FILES["photoDocument"] ?? null,
    $photoFolder,
    ["jpg", "jpeg", "png"],
    true
);

if ($academic_document === false) {
    exit("Please upload a valid academic result file (PDF, JPG or PNG, maximum 5 MB).");
}

if ($cnic_document === false) {
    exit("Please upload a valid CNIC / B-Form file (PDF, JPG or PNG, maximum 5 MB).");
}

if ($photo_document === false) {
    exit("Please upload a valid passport photo (JPG or PNG, maximum 5 MB).");
}

$sql = "INSERT INTO applications (
    application_id,
    first_name,
    last_name,
    cnic,
    dob,
    email,
    phone,
    gender,
    city,
    address,
    qualification,
    board,
    roll_number,
    passing_year,
    total_marks,
    obtained_marks,
    percentage,
    university,
    campus,
    program,
    session,
    academic_document,
    cnic_document,
    photo_document,
    status
) VALUES (
    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
    ?, ?, ?, ?, ?, ?, ?,
    ?, ?, ?, ?,
    ?, ?, ?,
    'Pending'
)";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    exit("Database query error: " . $conn->error);
}

$types = "sssssssssssssiiidsssssss";

$stmt->bind_param(
    $types,
    $application_id,
    $first_name,
    $last_name,
    $cnic,
    $dob,
    $email,
    $phone,
    $gender,
    $city,
    $address,
    $qualification,
    $board,
    $roll_number,
    $passing_year,
    $total_marks,
    $obtained_marks,
    $percentage,
    $university,
    $campus,
    $program,
    $session,
    $academic_document,
    $cnic_document,
    $photo_document
);

if ($stmt->execute()) {
    echo "SUCCESS|" . $application_id;
} else {
    echo "ERROR|" . $stmt->error;
}

$stmt->close();
$conn->close();
?>
