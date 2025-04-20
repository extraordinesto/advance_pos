<?php 
ob_start();
session_start();
$loginError = '';

if (!empty($_POST['email']) && !empty($_POST['pwd'])) {
    include 'Inventory.php';
    $inventory = new Inventory();
    $login = $inventory->login($_POST['email'], $_POST['pwd']); 

    if (!empty($login)) {
        $_SESSION['userid'] = $login['userid'];
        $_SESSION['name'] = $login['name'];            
        header("Location:index.php");
        exit(); // Don't forget the exit() after header redirection
    } else {
        $loginError = "Invalid email or password!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Point of Sales</title>
 <!-- Font Awesome -->
 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" integrity="sha512-KfkfwYDsLkIlwQp6LFnl8zNdLGxu9YAA1QvwINks4PhcElQSvqcyVLLD9aMhXd13uQjoXtEKNosOWaZqXgel0g==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<!-- Bootstrap -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
<!-- Font Awesome -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/js/all.min.js" integrity="sha512-6PM0qYu5KExuNcKt5bURAoT6KCThUmHRewN3zUFNaoI6Di7XJPTMoT6K0nsagZKk2OB4L7E3q1uQKHNHd4stIQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js" integrity="sha512-894YE6QWD5I59HgZOGReFYm4dnWc1Qt5NtvYSaNcOP+u1T9qYdvdihz0PPSiiqn/+/3e7Jo4EaG7TubfWGUrMQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
<!-- jQuery -->

<style>
html,
body,
body>.container {
    height: 95%;
    width: 100%;
}
body>.container {
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
}
#title {
    text-shadow:2px 2px 5px #000;
} 
</style>

<?php include('inc/container.php');?>

<h1 class="text-center my-4 py-3 text-light" id="title">Point of Sales - PHP</h1>    
<div class="col-lg-4 col-md-5 col-sm-10 col-xs-12">
    <div class="card rounded-0 shadow">
        <div class="card-header">
            <div class="card-title h3 text-center mb-0 fw-bold">Login</div>
        </div>
        <div class="card-body">
            <div class="container-fluid">
                <form method="post" action="">
                    <div class="form-group">
                        <?php if ($loginError) { ?>
                            <div class="alert alert-danger rounded-0 py-1"><?php echo $loginError; ?></div>
                        <?php } ?>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="control-label">Username</label>
                        <input name="email" id="email" type="text" class="form-control rounded-0" placeholder="Username" autofocus="" value="<?= isset($_POST['email']) ? $_POST['email'] : '' ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="control-label">Password</label>
                        <input type="password" class="form-control rounded-0" id="password" name="pwd" placeholder="Password" required>
                    </div>  
                    <div class="d-grid">
                        <button type="submit" name="login" class="btn btn-primary rounded-0">Login</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>        
