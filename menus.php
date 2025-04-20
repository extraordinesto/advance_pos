<?php
session_start(); // Start the session
?>

<nav class="navbar navbar-dark bg-dark bg-gradient navbar-expand-lg navbar-expand-md my-3">
	<div class="container-fluid">
		<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
			<ul class="nav navbar-nav menus">		
				<li class="nav-item"><a class="nav-link" href="index.php" id="index_menu">Home</a></li>			
			</ul>
		</div>
		<ul class="nav navbar-nav">
			<li class="dropdown position-relative">
				<button type="button" class="badge bg-light border px-3 text-dark rounded-pill dropdown-toggle" id="dropdownMenuButton1" data-bs-toggle="dropdown" data-bs-toggle="dropdown" aria-expanded="false">
					<span class="badge badge-pill bg-danger count"></span> 
					
					<?php 
					// Check if session variable 'name' is set before displaying
					if (isset($_SESSION['name'])) {
					    echo $_SESSION['name']; // Display the user's name
					} else {
					    echo "Guest"; // If no session is set, show "Guest"
					}
					?>
				</button>
				<ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
					<li><a class="dropdown-item" href="action.php?action=logout">Logout</a></li>
				</ul>
			</li>
		</ul>
	</div>
</nav>
