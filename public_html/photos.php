<!-- Plik ten składa się z dwóch częsci. 
	Pierwszą z nich jest galeria, która pobiera z bazy danych wszystkie lokalizacje zdjęć i wyświwtla je pokolei -->

<div class="row" id="gallery" data-toggle="modal" data-target="#modal">
	<?php
		// jeżeli podana jest zmienna show, to znaczy, że chcemy wyświetlać tylko zdjęcia z danej kategorii
	    if (isset($_GET['show']) && intval($_GET['show']) > 0) {
            $category = intval($_GET['show']);
			$stmt = $dbh->prepare("SELECT * FROM galery WHERE category = :category");
			$stmt -> execute([':category' => $category]);
		}else { 
			$stmt = $dbh->prepare("SELECT * FROM galery ORDER BY id ASC"); 
			$stmt -> execute();
		}

		$number = 0;
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			print '
				<div class="col-12 col-sm-6 col-md-3 col-lg-3">
					<a class="black-text" href="https://s58.labwww.pl/' . $row['file_name'] . '" data-target="#carousel" data-slide-to="' .$number. '">
						<img class="w-100" src="https://s58.labwww.pl/' . $row['file_name'] . '">
						<h3 class="text-center">' . htmlspecialchars($row['title'], ENT_QUOTES | ENT_HTML401, 'UTF-8') . '</h3>
					</a>
				</div>';
			$number++;
		}?>
</div>

<!-- Drugą częścią jest Modal z biblioteki bootstrap. 
	Jest to element, który wyświetla się "ponad" stroną.
	Wewnątrz modala znajduje się interaktywny element Carousel, 
	który pozwala nam płynnie przechodzić między zdjęciami  -->
<div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-hidden="true">
<div class="modal-dialog modal-xl" role="document">
<div class="modal-content">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		</button>
	</div>
	<div class="modal-body">
		<div id="carousel" class="carousel slide" data-ride="carousel">

			<!-- Wyświetlanie znaczników na dole zdjęcia -->
			<ol class="carousel-indicators">
				<?php
				$stmt -> execute();
				$number = 0;
				while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
						print '<li data-target="#carousel" data-slide-to="' .$number. '"></li>';
						$number++;
				}
				?>
			</ol>
			
			<!-- Wyświetlanie zdjęcia, tytułu oraz opisu -->
			<div class="carousel-inner">
				<?php
				$stmt -> execute();
				$isFirst = true; // Pierwszy element musi mieć klasę "active", bo inaczej nie działa
				while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
					if ($isFirst){
						print '<div class="carousel-item active">';
						$isFirst = false;
					} else{
						print '<div class="carousel-item">';
					}
					print '
					<a href="/photo/show/'.$row['id'].'">
						<img class="d-block w-100" src="https://s58.labwww.pl/' . $row['file_name'] . '" alt="' . $row['title'] . '"></a>
						<div class="carousel-caption d-none d-md-block">
							<h5 class="opis">' . htmlspecialchars($row['title'], ENT_QUOTES | ENT_HTML401, 'UTF-8') . '</h5>
							<p class="opis">' . htmlspecialchars($row['description'], ENT_QUOTES | ENT_HTML401, 'UTF-8') . '</p>
						</div>
					</div>';
				}?>
			</div>

				<!-- Przyciski do przewijania -->
			<a class="carousel-control-prev" href="#carousel" role="button" data-slide="prev">
			<span class="carousel-control-prev-icon" aria-hidden="true"></span>
			<span class="sr-only">Previous</span>
			</a>
			<a class="carousel-control-next" href="#carousel" role="button" data-slide="next">
			<span class="carousel-control-next-icon" aria-hidden="true"></span>
			<span class="sr-only">Next</span>
			</a>
		</div>
	</div>
</div>
</div>
</div>