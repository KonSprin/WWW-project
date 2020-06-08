<?php

if (isset($_GET['show']) && intval($_GET['show']) > 0) {
    $id = intval($_GET['show']);

    // podstrona photo/show/<id>,
    // wyświetla konkretne zdjęcie
    // oraz umożliwia jego edyję i usunięcie, pod warunkiem, że wyświetla je użytkownik, który je dodał

    $stmt = $dbh->prepare("SELECT * FROM galery WHERE id = :id");
    $stmt -> execute([':id' => $id]);
    $img = $stmt->fetch();

    if (mb_strlen($img['title']) > 0){
        print '
        <div class="row">
        <div class="col-12">
            <div class="card">
                <img class="card-img-top" src="/' . $img['file_name'] . '">
                <div class="card-body">
                    <h3 class="card-title">' . htmlspecialchars($img['title'], ENT_QUOTES | ENT_HTML401, 'UTF-8') . '</h3>
                    <p class="car-text">' . htmlspecialchars($img['description'], ENT_QUOTES | ENT_HTML401, 'UTF-8') . '</p>';
                    if (isset($_SESSION['id']) && $img['user_id'] == $_SESSION['id']){
                        print '
                        <div class="dropdown">
                            <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                Akcja
                            </button>
                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                <a class="dropdown-item" href="https://s58.labwww.pl/photo/edit/'. $id .'">Edytuj</a>
                                <a class="dropdown-item" href="https://s58.labwww.pl/photo/delete/'. $id .'">Usuń</a>
                            </div>
                        </div>';
                        } 
        print'
                </div>
            </div>
        </div>
        </div>';
    }else {
        print '<p style="font-weight: bold; color: red;">Nie ma takiego zdjęcia</p>';
    }
} elseif (isset($_GET['edit']) && intval($_GET['edit']) > 0) {
    $id = intval($_GET['edit']);

    if (isset($_POST['title']) && isset($_POST['description'])) {

				$desc = $_POST['description'];
                $title = $_POST['title'];
                $category = $_POST['category'];
                $user_id = (isset($_SESSION['id']) ? $_SESSION['id'] : 0);
                
                $stmt = $dbh->prepare("UPDATE galery SET title = :title, description = :description, category = :category WHERE id = :id AND user_id = :user_id");
                $stmt->execute([':id' => $id, ':user_id' => $user_id, ':title' => $title, ':description' => $desc, 'category' => $category]);

                print '<p style="font-weight: bold; color: green;">Dane zostały edytowane.</p>';

    }

    // podstrona /photo/edit/<id>,
    // tutaj wyswietlamy formularz edycji zdjęcia, ktorego ID mamy w zmiennej $id
    // wyświetlany jest formulaż podobly do tego z add_photo.php, poza samym dodanie pliku
	
	$stmt = $dbh->prepare("SELECT * FROM galery WHERE id = :id");
	$stmt -> execute([':id' => $id]);
	$img = $stmt->fetch();
	
	if (mb_strlen($img['title']) > 0){
        print'
        <form action="https://s58.labwww.pl/photo/edit/'. $id .'" method="POST">
			<div class="form-group">
				<label for="inputTitle">Tytuł zdjęcia</label>
				<input type="text" name="title" id="inputTitle" value="'.htmlspecialchars($img['title']).'" class="form-control">
			</div>
			<div class="form-group">
				<label for="inputDes">Opis zdjęcia</label>
				<input type="text" name="description" id="inputDes" value="'.htmlspecialchars($img['description']).'" class="form-control">
			</div>
			<div>
				<label for="category">Kategoria</label>
				<select name="category" id="category" class="form-control">';
					$stmt = $dbh->prepare("SELECT * FROM categories");
					$stmt -> execute();
					while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
						print '<option value=" '. $row['id'] .' "> '. $row['name'] .' </option>';
                    }
            print'
				</select>
			</div>
			<div class="form-group">
				<input type="submit" name="submit"></button>
			</div>
        </form>';
        
	}else {
		print '<p style="font-weight: bold; color: red;">Nie ma takiego zdjęcia</p>';
	}

} elseif (isset($_GET['delete']) && intval($_GET['delete']) > 0){

    // podstrona /photo/delete/<id>,
    // usuwanie zdjęcia po prostu wykasowując wpis o nim z bazy danych. 
    // zdjęcie fizycznie dalej zostaje na serwerze, bo jak wiadomo w internecie nic nie ginie
    
    $id = intval($_GET['delete']);
    $user_id = (isset($_SESSION['id']) ? $_SESSION['id'] : 0);
    $stmt = $dbh->prepare("DELETE FROM galery WHERE id = :id AND user_id = :user_id");
    $stmt -> execute([':id' => $id, ':user_id' => $user_id]);

    print '<p style="font-weight: bold; color: green;">Zdjęcie zostało usunięte</p>';
} else{
    header('Location: /');
}