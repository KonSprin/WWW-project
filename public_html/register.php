<div class="card-body">
	<?php
		// Prosty formulaż rejestracyjny, w którym wystarzcy podać email i hasło
		// Wszystko zostało tak jak po laboratorium z Panem Zaworskim
		if (!defined('IN_INDEX')) { exit("Nie można uruchomić tego pliku bezpośrednio."); }

		if (isset($_POST['mail']) && isset($_POST['passwd']) && isset($_POST['g-recaptcha-response'])) {
			$recaptcha = new \ReCaptcha\ReCaptcha($config['recaptcha_private']);
			$resp = $recaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);
			
			if ($resp->isSuccess()) {
				$passwd = $_POST['passwd'];
				$email = $_POST['mail'];
				if (mb_strlen($passwd) >= 4 && mb_strlen($passwd) <= 40 && preg_match('/^[a-zA-Z0-9\-\_\.]+\@[a-zA-Z0-9\-\_\.]+\.[a-zA-Z]{2,5}$/D', $email)) {
					$passwd = password_hash($passwd, PASSWORD_DEFAULT);
					try {
						$stmt = $dbh->prepare('
							INSERT INTO users (
								id, email, password, created
							) VALUES (
								null, :email, :password, NOW()
							)
						');
						$stmt->execute([':email' => $email, ':password' => $passwd]);
						print '<span style="color: green;">Konto zostało założone.</span>';
					} catch (PDOException $e) {
						print '<span style="color: red;">Podany adres email jest już zajęty.</span>';
						print $e;
					}  
				} else {
					print '<p style="font-weight: bold; color: red;">Podane dane są nieprawidłowe.</p>';
				}	
			} else {
				$errors = $resp->getErrorCodes();
				print '<p style="font-weight: bold; color: red;">Pojawił się błąd z reCaptcha.</p>';
			}
		}
	?>

	<form action="/register" method="POST">
		<input type="text" name="mail" placeholder="email">
		<input type="password" name="passwd" placeholder="hasło">
		<div class="g-recaptcha" data-sitekey=<?php print$config['recaptcha_public'];?>></div>
		<input type="submit" value="Załóż Konto">
	</form>
</div>