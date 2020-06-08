<?php
    session_start();

    ini_set('display_errors', 1);
    error_reporting(E_ALL);
	
	define("IN_INDEX", 1);
	
	require __DIR__ . '/vendor/autoload.php';

    include("config.inc.php");
    include("functions.inc.php");

    // Wczytywanie pliku konfiguracyjnego
    if (isset($config) && is_array($config)) {

        try {
            $dbh = new PDO('mysql:host=' . $config['db_host'] . ';dbname=' . $config['db_name'] . ';charset=utf8mb4', $config['db_user'], $config['db_password']);
            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            print "Nie mozna polaczyc sie z baza danych: " . $e->getMessage();
            exit();
        }

    } else {
        exit("Nie znaleziono konfiguracji bazy danych.");
    }
    
    // Sprawdzanie czy użytkownik jest zalogowany
	if (isset($_POST['login']) && isset($_POST['password'])) {
		$stmt = $dbh->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute([':email' => $_POST['login']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
			if (password_verify($_POST['password'], $user['password'])) {
				        $_SESSION['id'] = $user['id'];
						$_SESSION['email'] = $user['email'];
            }
        }
	}
    
    // Wylogowywanie użytkownika
	if (isset($_GET['logout'])) {
		unset($_SESSION['id']);
		unset($_SESSION['email']);
    }

    // Użytkownicy on-line
    if (isset($_SESSION['id'])) {
        $stmt = $dbh->prepare("UPDATE users SET last_seen = NOW() WHERE id = :id");
        $stmt->execute([':id' => $_SESSION['id']]);
    }
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Strona <?php print domena(); ?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <link rel="stylesheet" href="https://s58.labwww.pl/style.css">
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
        <script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>        
        <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
        <script src="https://rawgit.com/enyo/dropzone/master/dist/dropzone.js"></script>
    </head>
    <body>
        <!-- Pasek nawigacyjny -->
        <nav class="navbar navbar-expand-sm navbar-dark bg-dark fixed-top">
          <div class="container">
          <a class="navbar-brand" href="#"><?php print domena(); ?></a>
          <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
          </button>
          <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav" id="menu-buttons">
              <li class="nav-item active">
                <a class="nav-link" href="/">Strona główna</span></a>
              </li>
              <!-- W zależności czy użytkownik jest zalogowany, wyświetlana jest opcja Rejestracja lub Dodaj zdjęcie -->
			  <?php navitem() ?>
              <li class="nav-item">
                <a class="nav-link" href="/category">Kategorie</span></a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="/instruction">Instrukcja</span></a>
              </li>
            </ul>
          </div>
          
          <!-- Zaloguj/Wyloguj się -->
		  <?php
			if (isset($_SESSION['id']) && isset($_SESSION['email'])){
				print
				 '<form action="/index.php?logout=1" method="POST" class="form-inline my-2 my-lg-0">
					  <a id="email">' . $_SESSION['email'] . '</a>
					  <button class="btn btn-outline-info my-2 my-sm-0" type="submit">Wyloguj się</button>
				  </form>';
			} else{
				print
				 '<form action="" method="POST" class="form-inline my-2 my-lg-0">
					  <input type="text" name="login" class="form-control mr-sm-2" placeholder="Login" aria-label="login" style="width: 150px;">
					  <input type="password" name="password" class="form-control mr-sm-2" placeholder="Hasło" aria-label="password" style="width: 150px;">
					  <button class="btn btn-outline-info my-2 my-sm-0" type="submit">Zaloguj się</button>
				  </form>';
			}
		  ?>
          </div>
        </nav>

        <div class="jumbotron">
            <div class="container">
                <h1 class="display-4">Galeria zdjęć</h1>
                <p class="lead">Możesz dodawać zdjęcia lub podziwiać obrazy innych</p>
            </div>
        </div>

        <div class="container mb-5">
            <!-- W tym kontenerze wyświetlana jest główna zawartość strony -->
            <?php
                // tylko na te strony można się dostać
                // zabieg zrobiony w celu uniemożliwienia użytkownikowi podróżowania po drzewie plików
                $allowed_pages = ['photos', 'add_image', 'register', 'index', 'photo', 'category', 'instruction'];
                // dodać zdjęcie może tylko użytkownik zalogowany
                $protected_pages = ['add_image'];

                if (isset($_GET['page']) && $_GET['page'] && in_array($_GET['page'], $allowed_pages) && (!in_array($_GET['page'], $protected_pages) || isset($_SESSION['id']))) {
                    if (file_exists($_GET['page'] . '.php')) {
                        include($_GET['page'] . '.php');
                    } else {
                        print 'Plik ' . $_GET['page'] . '.php nie istnieje.';
                    }
                } else {
                    include('photos.php');
                }
            ?>
        </div>

        <footer class="footer mt-auto" style="background-color: #f5f5f5;">
          <div class="container">
            <span class="text-muted">Ostatnia aktualizacja: 08.06.2020</span>
          </div>
        </footer>

    </body>
</html>

<script language="JavaScript">
  /**
    * Skrypt ten sprawia, że użytkownik nie może kliknąć prawym przyciskiem myszy na stronie
    * Zablokowane są też najpopularniejsze skróty klawiszowe przywołujące konsolę
    * By Arthur Gareginyan (https://www.arthurgareginyan.com)
    */
  window.onload = function() {
    document.addEventListener("contextmenu", function(e){
      e.preventDefault();
    }, false);

    document.addEventListener("keydown", function(e) {
      // "I" key
      if (e.ctrlKey && e.shiftKey && e.keyCode == 73) {
        disabledEvent(e);
      }
      // "J" key
      if (e.ctrlKey && e.shiftKey && e.keyCode == 74) {
        disabledEvent(e);
      }
      // "S" key + macOS
      if (e.keyCode == 83 && (navigator.platform.match("Mac") ? e.metaKey : e.ctrlKey)) {
        disabledEvent(e);
      }
      // "U" key
      if (e.ctrlKey && e.keyCode == 85) {
        disabledEvent(e);
      }
      // "F12" key
      if (event.keyCode == 123) {
        disabledEvent(e);
      }
    }, false);

    function disabledEvent(e){
      if (e.stopPropagation){
        e.stopPropagation();
      } else if (window.event){
        window.event.cancelBubble = true;
      }
      e.preventDefault();
      return false;
    }
  };
</script>