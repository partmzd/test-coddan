<?php

require 'vendor/autoload.php';

use GuzzleHttp\Client;

$searchClient = new Client([
    'base_uri' => 'https://api.github.com/search/repositories',
]);

$perPage = 20;
$currentPage = 1;
$repositoryFullName = null;
$repositoryPagesCount = 0;
$repositoriesCount = 0;
$isIncompleteResult = false;
$repositories = [];

if (isset($_GET['repository_full_name']) && !empty($_GET['repository_full_name'])) {
    $repositoryFullName = stripcslashes(htmlspecialchars($_GET['repository_full_name']));
}

if ((int)$_GET['page'] >= 0) {
    $currentPage = (int)$_GET['page'];
}

if ($repositoryFullName !== null) {
    $uri = "?q={$repositoryFullName}+in:name+language:php+language:javascript&sort=stars&order=desc&per_page={$perPage}&page={$currentPage}";

    $response = $searchClient->get($uri);

    $repositoriesResponse = json_decode($response->getBody()->getContents(), true);
    $repositories = $repositoriesResponse['items'];
    $repositoryPagesCount = floor($repositoriesResponse['total_count'] / $perPage);

    if (($repositoriesResponse['total_count'] % $perPage) > 0) {
        $repositoryPagesCount++;
    }

    $repositoriesCount = $repositoriesResponse['total_count'];

    if (!empty($repositoriesResponse['incomplete_results'])) {
        $isIncompleteResult = true;
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
          integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

    <!-- Optional theme -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css"
          integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

    <!-- Latest compiled and minified JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"
            integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa"
            crossorigin="anonymous"></script>

    <title>GitHub search page</title>
</head>
<body>

<div class="container">
    <h1>GitHub search tool</h1>

    <form>
        <div class="form-group">
            <label for="repositoryFullNameInput">What do you search?</label>
            <input type="text" class="form-control" id="repositoryFullNameInput" placeholder="Repository name"
                   name="repository_full_name" value="<?= $repositoryFullName ?>">
        </div>
        <button type="submit" class="btn btn-default">Find it</button>
    </form>

    <h3>Result list</h3>
    <?php if ($isIncompleteResult === true): ?>
        <div class="alert alert-warning alert-dismissible" role="alert">
            <strong>Warning!</strong> Found: more than <?= $repositoriesCount ?> repositories. Please specify your request.
        </div>
    <?php else: ?>
        <h6>Found: <?= $repositoriesCount ?> repositories</h6>
    <?php endif ?>

    <table class="table table-hover">
        <thead>
        <tr>
            <th>Owner</th>
            <th>Repository full name</th>
            <th>Language</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($repositories as $repository): ?>
            <tr>
                <td><img width="50" src="<?= $repository['owner']['avatar_url'] ?>"></td>
                <td><a href="<?= $repository['html_url'] ?>" target="_blank"><?= $repository['full_name'] ?></a></td>
                <td><?= $repository['language'] ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <ul class="pagination">
        <?php
        for ($i = 1; $i <= $repositoryPagesCount; $i++):?>
            <li><a href="?repository_full_name=<?= $repositoryFullName ?>&page=<?= $i ?>"><?= $i ?></a></li>
        <?php endfor; ?>
    </ul>
</div>

</body>
</html>
