<!--------f-----------

    Project 1
    Name: Oksana Sokolova
    Date: 
    Description:

--------------------->
<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Contact us</title>
		<link rel="stylesheet" href="styles.css">
		<script src="script.js"></script>
	</head>
	<body class="contacts">
		<!-- Main Navigation -->
		<nav id="navigation1" aria-label="Main Navigation">
			<img class="logo" src="images/croissant.png" alt="logo">
			<nav>
				<ul class="list1">
					<li><a href="index.php">HOME </a></li>
					<li><a href="menu.php">PRODUCTS </a></li>
					<li><a href="contact.php">CONTACT US</a></li>
				</ul>
            </nav>
            <div class="search">
            	<input  id="search-input" type="search" placeholder="Search..." >
				<button>SEARCH</button>
			</div>	
		</nav>
    <div id="box">
	    	<header class="contacts">
	    		 <!-- Contact Us Page Header -->
			    <h1>Contact us</h1>
				<h2>We’d love to see you during our working hours!</h2>
				<!-- Operating Hours -->
				<ul>
					<li>Monday to Friday: 7:00 AM – 7:00 PM</li>
					<li>Saturday: 8:00 AM – 5:00 PM</li>
					<li>Sunday: We’re taking a break!</li>
				</ul>
				<!-- Contact Information -->
				<h2>Phone</h2>
				<p>+1(201)321-1111</p>
				<h2>Email</h2>
				<p>sweetdelights@gmail.com</p>
			</header>
			<!-- Feedback Form -->
			<form id="feedback" action="index.php" method="POST">
				<fieldset>
					<legend>Your feedback is important for us.</legend>
					<ul>
						<li>
							<label for="name">Name</label>
							<input id="name" name="name" type="text">
							<p class="error"  id="name_error">* Enter your name</p>
						</li>
						<li>
							<label for="phone">Phone</label>
							<input id="phone" name="phone" type="tel">
							<p class="error" id="phone_error">* Enter your phone number</p>
							<!--<p class="error" id="phoneformat_error">* Invalid phone number</p>-->
						</li>
						<li>
							<label for="email">Email</label>
							<input id="email" name="email" type="email">
							<p class="error" id="email_error">* Enter your email address</p>
							<!--<p class="error" id="emailformat_error">* Invalid email address</p>-->
						</li>
						<li>
							<label for="text">Comment</label>
							<textarea id="text" name="text" rows="10"></textarea>
						</li>
					</ul>
					<button type="submit">Submit</button>
					<button type="reset">Reset</button>
				</fieldset>	
			</form>
    </div>
        <!-- Footer -->
		<footer>
			<nav>
				<ul class="list2">
					<li><a href="index.php">HOME </a></li>
					<li><a href="menu.php">PRODUCTS </a></li>
					<li><a href="contact.php">CONTACT US</a></li>
				</ul>
            </nav>
            <p class="copyright">Copyright &#169; 2011 Sweet Delights Bakery</p>
            <div class="address">
            	<p> Central Park West at 79th Street, New York, NY 100024-5192</p> 
            </div>
		</footer>
	</body>
</html>			