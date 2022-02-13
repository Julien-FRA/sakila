<?php

require('src/URLFormat.php');
require('src/TableFormat.php');

use App\URLFormat;
use App\TableFormat;

// Connexion à la bdd
require_once('connect.php');

$requestMain = 'SELECT film.title, film.rental_rate, film.rating, count(inventory.film_id) as rental_nbr, category.name as category
                FROM film,category,film_category, inventory, rental
                WHERE film.film_id = film_category.film_id 
                AND category.category_id = film_category.category_id
                AND rental.inventory_id = inventory.inventory_id
                AND inventory.film_id = film.film_id';

$requestCount = 'SELECT COUNT(film.film_id) AS nbFilm FROM film';

$params = [];
$sortable = ["title", "category", "rental_rate", "rental_nbr", "rating"];

// Recherche
if (!empty($_GET['q'])) {
    $requestMain .= " AND film.title LIKE :title";
    $requestCount .= " WHERE film.title LIKE :title";
    $params['title'] = "%" . $_GET['q'] . "%";
}

$requestMain .= " GROUP BY inventory.film_id";

// Tri
if (!empty($_GET['sort']) && in_array($_GET['sort'], $sortable)) {
    $direction = $_GET['dir'] ?? 'asc';
    if (!in_array($direction, ['asc', 'desc'])) {
        $direction  = 'asc';
    }
    $requestMain .= " ORDER BY " . $_GET['sort'] . " $direction";
}

// Pagination
$page = (int) ($_GET['p'] ?? 1);
$perPage = (int) ($_GET['perPage'] ?: 20);
$offset = ($page - 1) * $perPage;
$requestMain .= " LIMIT " . $perPage . " OFFSET $offset";

$pdoMain = $pdo->prepare($requestMain);
$pdoMain->execute($params);
$reponseMain = $pdoMain->fetchAll(PDO::FETCH_ASSOC);

$pdoCount = $pdo->prepare($requestCount);
$pdoCount->execute($params);
$reponseCount = (int)$pdoCount->fetch()['nbFilm'];
$pages = ceil($reponseCount / $perPage);

require_once('close.php');

?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGBDR</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
</head>

<body>
    <main class="container">
        <div class="row">
            <section class="col-12">
                <h1>Liste des films</h1>
                <form action="" class="form-search">
                    <div class="form-group">
                        <input type="text" class="form-control" name="q" placeholder="Rechercher par titre" value="<?= htmlentities($_GET['q'] ?? null) ?>">
                    </div>
                    <button class="btn btn-primary">Rechercher</button>
                </form>
                <form action="" class="form-number">
                    <label for="">Nombre de film par page : </label>

                    <select name="perPage">
                        <option value="">--Please choose an option--</option>
                        <option value="5">5</option>
                        <option value="10">10</option>
                        <option value="15">15</option>
                        <option value="20">20</option>
                        <option value="25">25</option>
                        <option value="30">30</option>
                    </select>
                    <button class="btn btn-primary btn-nb">Afficher</button>
                </form>
                <table class="table">
                    <thead>
                        <th class="title"><?= TableFormat::sort('title', 'Title', $_GET) ?></th>
                        <th class="category"><?= TableFormat::sort('category', 'Category', $_GET) ?></th>
                        <th class="price"><?= TableFormat::sort('rental_rate', 'Prix de location', $_GET) ?></th>
                        <th class="rental"><?= TableFormat::sort('rental_nbr', 'Nb de locations', $_GET) ?></th>
                        <th class="rate"><?= TableFormat::sort('rating', 'Classement', $_GET) ?></th>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($reponseMain as $data) {
                        ?>
                            <tr>
                                <td><?= $data['title']; ?></td>
                                <td><?= $data['category']; ?></td>
                                <td><?= $data['rental_rate']; ?></td>
                                <td><?= $data['rental_nbr']; ?></td>
                                <td><?= $data['rating']; ?></td>
                            </tr>
                        <?php
                        }
                        ?>
                    </tbody>
                </table>
                <div class="pag-nav">
                    <?php if ($pages > 1 && $page > 1) : ?>
                        <a href="?<?= URLFormat::withParam($_GET, "p", $page - 1) ?>" class="btn btn-primary">Précédente</a>
                    <?php endif; ?>
                    <p><?= $_GET['p'] ?: 1 ?> / <?= $pages; ?></p>
                    <?php if ($pages > 1 && $page < $pages) : ?>
                        <a href="?<?= URLFormat::withParam($_GET, "p", $page + 1) ?>" class="btn btn-primary">Suivante</a>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </main>
</body>

</html>

<?php

// Le nom du film, le prix de location, le classement //
// SELECT film.title, film.rental_rate, film.rating
// FROM film;

// Le nom du genre du film //
// SELECT film.title, category.name
// FROM film, film_category, category
// WHERE film.film_id = film_category.film_id
// AND film_category.category_id = category.category_id
// GROUP BY name;

// Le nombre de fois que le film a été loué //
// SELECT film.title, rental.rental_id, film.film_id
// FROM film, rental, inventory
// WHERE film.film_id = inventory.film_id
// AND inventory.inventory_id = rental.inventory_id
// GROUP BY film_id;

// Sélection général //
// SELECT film.film_id, film.title, film.rental_rate, film.rating, category.name, rental.rental_id
// FROM film, film_category, category, rental, inventory
// WHERE film.film_id = film_category.film_id
// AND film_category.category_id = category.category_id
// AND film.film_id = inventory.film_id
// AND inventory.inventory_id = rental.inventory_id
// GROUP BY film_id;