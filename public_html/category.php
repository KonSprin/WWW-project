<!-- Ta podstrona służy do zarządzania kategoriami, posiada trzy karty, których nazwy mówią same za sibie -->

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h3 class="card-title">Wybierz Kategorię</h3>
                <!-- Bardzo podobny przycisk jak na głównej stornie. Ten jednak nie musi aktualizować nazwy -->
                <div class="dropdown">
                    <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Kategoria
                    </button>
                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        <?php
                            $stmt = $dbh->prepare("SELECT * FROM categories");
                            $stmt -> execute();
                            
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                                print '<a class="dropdown-item" href="photos/show/'. $row['id'] .'">'. $row['name'] .'</a>';
                            }  
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
            <?php
                if (isset($_POST['submit']) && isset($_POST['name'])) {
                    if (mb_strlen($_POST['name']) > 0) {
                        $name = $_POST['name'];

                        $stmt = $dbh->prepare("INSERT INTO categories (name) VALUES (:name)");
                        $stmt -> execute([':name' => $name]);
                        print '<p style="font-weight: bold; color: green;">Kategoria dodana pomyślnie</p>';
                    } else {
                        print '<p style="font-weight: bold; color: red;">Musisz podaj nazwę</p>';
                    }
                }
            ?>

                <h3 class="card-title">Dodaj Kategorię</h3>
                <form action="/category" method="POST">
                    <div class="form-group">
                        <input type="text" name="name" placeholder="Nowa nazwa" class="form-control">
                    </div>
                    <div class="form-group">
                        <input type="submit" name="submit"></button>
                    </div>
                </form>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <h3 class="card-title">Usuń Kategorię</h3>
                <?php        
                    if (isset($_POST['categoryDelete'])) {
                        $id = intval($_POST['categoryDelete']);
                        $stmt = $dbh->prepare("DELETE FROM categories WHERE id = :id");
                        $stmt -> execute([':id' => $id]);
                
                        print '<p style="font-weight: bold; color: green;">Kategoria usunięta pomyślnie</p>';
                    } ?>
                <form action="/category" method="POST">
                    <div class="form-group">
                        <select name="categoryDelete" class="form-control">
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
                        <input type="submit" name="submit"></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>