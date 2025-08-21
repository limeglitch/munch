<?php
session_start();

// Admin
$admin_user = '286791';
$admin_pass_hash = '$2y$10$w9Z7H7gFf7zXcH9D8V3zXeJf7Z9P1uHq6';

// DB
$host = 'localhost';
$db   = 'munch_db';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     exit('Database connection failed');
}

if(isset($_GET['logout'])){
    session_destroy();
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

if(isset($_POST['login'])){
    if($_POST['username']===$admin_user && password_verify($_POST['password'], $admin_pass_hash)){
        $_SESSION['admin']=true;
    } else {
        $error="خطأ في بيانات الدخول";
    }
}

if(isset($_POST['add_product']) && isset($_SESSION['admin'])){
    $title = $_POST['title'];
    $desc = $_POST['description'];
    $price = $_POST['price'];
    $image = $_FILES['image']['name'];
    $target = "uploads/".basename($image);
    if(!is_dir('uploads')) mkdir('uploads',0777,true);
    if(move_uploaded_file($_FILES['image']['tmp_name'],$target)){
        $stmt=$pdo->prepare("INSERT INTO products(title,description,price,image) VALUES(?,?,?,?)");
        $stmt->execute([$title,$desc,$price,$image]);
    }
}

$stmt=$pdo->query("SELECT * FROM products ORDER BY created_at DESC");
$products=$stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ar">
<head>
<meta charset="UTF-8">
<title>Munch Dashboard</title>
<style>
body{margin:0;font-family:'Segoe UI',sans-serif;background:#f5f6fa;}
.header{background:#e84118;color:#fff;padding:25px;text-align:center;font-size:28px;font-weight:bold;letter-spacing:2px;box-shadow:0 5px 15px rgba(0,0,0,0.2);}
.container{width:90%;max-width:1200px;margin:auto;padding:25px;}
button{cursor:pointer;border:none;padding:12px 25px;border-radius:10px;background:#e84118;color:#fff;font-weight:bold;transition:all 0.3s ease;}
button:hover{background:#c23616;transform:scale(1.05);}
input,textarea{width:100%;padding:12px;margin:5px 0;border-radius:8px;border:1px solid #dcdde1;transition:all 0.3s ease;}
input:focus,textarea:focus{border-color:#e84118;outline:none;transform:scale(1.02);}
form{background:#fff;padding:25px;border-radius:15px;box-shadow:0 0 20px rgba(0,0,0,0.1);margin-bottom:25px;}
.products{display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:25px;}
.product{background:#fff;padding:20px;border-radius:15px;box-shadow:0 0 15px rgba(0,0,0,0.1);text-align:center;transition:all .3s;}
.product img{max-width:100%;border-radius:15px;margin-bottom:15px;transition:all 0.3s;}
.product:hover{transform:scale(1.05);box-shadow:0 10px 25px rgba(0,0,0,0.2);}
.logout{float:right;color:#fff;background:#2f3640;padding:10px 20px;border-radius:10px;text-decoration:none;font-weight:bold;transition:all 0.3s;}
.logout:hover{background:#353b48;transform:scale(1.05);}
.preview-img{max-width:180px;margin-top:10px;border-radius:10px;transition:all 0.3s;}
@media(max-width:600px){.products{grid-template-columns:1fr;}}
</style>
</head>
<body>
<div class="header">Munch</div>
<div class="container">

<?php if(!isset($_SESSION['admin'])): ?>
<h2>تسجيل دخول Admin</h2>
<form method="post">
<input type="text" name="username" placeholder="Username" required>
<input type="password" name="password" placeholder="Password" required>
<button type="submit" name="login">Login</button>
<?php if(isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
</form>
<?php else: ?>
<a href="?logout=1" class="logout">Logout</a>
<h2>لوحة تحكم Admin</h2>
<h3>إضافة منتج جديد</h3>
<form method="post" enctype="multipart/form-data">
<input type="text" name="title" placeholder="Title" required>
<textarea name="description" placeholder="Description"></textarea>
<input type="number" step="0.01" name="price" placeholder="Price" required>
<input type="file" name="image" id="imageInput" required>
<img id="preview" class="preview-img" style="display:none;">
<button type="submit" name="add_product">Add Product</button>
</form>

<h3>المنتجات</h3>
<div class="products">
<?php foreach($products as $p): ?>
<div class="product">
<img src="uploads/<?php echo htmlspecialchars($p['image']); ?>" alt="">
<h4><?php echo htmlspecialchars($p['title']); ?></h4>
<p><?php echo htmlspecialchars($p['description']); ?></p>
<b>$<?php echo $p['price']; ?></b>
</div>
<?php endforeach; ?>
</div>

<script>
const input=document.getElementById('imageInput');
const preview=document.getElementById('preview');
input.addEventListener('change',e=>{
    const file=e.target.files[0];
    if(file){
        preview.src=URL.createObjectURL(file);
        preview.style.display='block';
    }
});
</script>

<?php endif; ?>
</div>
</body>
</html>
