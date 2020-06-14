<div class="row">
	<div class="card-body">
		<?php
			if (!defined('IN_INDEX')) { exit("Nie można uruchomić tego pliku bezpośrednio."); }
				
			// generalnie idea jest taka, że samo zdjęcie dodajemy do folderu galery bezpośrednio na serwerze, a jego nazwę tytuł itp sysyłamy do bazy danych
			if (isset($_POST['submit'])) {
				if (isset($_POST['title']) && mb_strlen($_POST['title']) > 2) {
					$file = $_FILES['file'];
					$fileName = $file['name'];
					$fileTmp = $file['tmp_name'];
					$fileSize = $file['size'];
					$fileError = $file['error'];

					$tmp = explode('.', $fileName);
					$fileExt = strtolower(end($tmp)); //sprawdzenie jakie rozszerzenie ma przesłany plik

					$allowedExt = array('jpg', 'jpeg', 'png'); // dozwolone rozszerzenia plików
					
					if ($fileError === 0) {
						if (in_array($fileExt, $allowedExt)) {
							// if (intval($fileSize) < 1048576‬) {    // tu powinno być sprawdzanie, czy plik nie przekracza wielkości 1MB, ale z nieznanych mi przyczyn interpreter wyrzuca błąd
								$fileNameNew = uniqid('', true) . "." . $fileExt;
								$fileDest = 'galery/' . $fileNameNew;
								move_uploaded_file($fileTmp, $fileDest);		

								$title = $_POST['title'];
								$id = $_SESSION['id'];
								$category = $_POST['category'];
								$description = $_POST['description'];
								$stmt = $dbh->prepare("INSERT INTO galery (user_id, title, file_name, category, description, created) VALUES (:user_id, :title, :file_name, :category, :description, NOW())");
								$stmt->execute([':user_id' => $id, ':title' => $title, ':file_name' => $fileDest, ':category' => $category, ':description' => $description]);
								print '<p style="font-weight: bold; color: green;">Zdjęcie dodane pomyślnie</p>';
							// } else {
							// 	print '<p style="font-weight: bold; color: red;">Zdjęcie jest zbyt duże, maksymalny rozmiar to 1MB</p>';
							// }
						} else {
							print '<p style="font-weight: bold; color: red;">Można dodać tylko pliki z rozszerzeniami: .jpg, .jpeg, .png</p>';
						}
					} else {
						print '<p style="font-weight: bold; color: red;">Wystąpił nieoczekiwany błąd przy dodawaniu pliku</p>';
					}
				} else {
					print '<p style="font-weight: bold; color: red;">Musisz podaj tytuł, który ma co najmniej 3 znaki</p>';
				}
			}
		?>

		<!-- formulaż, którym dodajemy zdjęcia
			tytuł i opis, to po prostu pola tekstowe, 
			natomiast kategoria, to rozwijany przycisk, który pobiera z bazy danych wszystkie kategorie
			jest też tu pole Drag&Drop zaimplementowane za pomocą biblioteki dropzone.js -->
		<form action="/add_image" method="POST" enctype="multipart/form-data">
			<div class="form-group">
				<label for="inputTitle">Tytuł zdjęcia</label>
				<input type="text" name="title" id="inputTitle" placeholder="Twój tytuł" class="form-control">
			</div>
			<div class="form-group">
				<label for="inputDes">Opis zdjęcia</label>
				<input type="text" name="description" id="inputDes" placeholder="Krótki opis" class="form-control">
			</div>
			<div>
				<label for="category">Kategoria</label>
				<select name="category" id="category" class="form-control">
					<?php 
					$stmt = $dbh->prepare("SELECT * FROM categories");
					$stmt -> execute();
					while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
						print '<option value=" '. $row['id'] .' "> '. $row['name'] .' </option>';
					}
					?>
				</select>
			</div>
			<div class="form-group">
				<label for="drop">Przeciągnij zdjęcie</label>
				<input type="file" name="file" id="drop" class="dropzone">
			</div>
			<div class="form-group">
				<input type="submit" name="submit"></button>
			</div>
		</form>
	</div>
</div>
