<navbar>
    <ul>
        <li class="navbar-point"></li>
        <a href="index.php"><li class="navbar-menu-button"><div class="navbar-menu-button-img" <?php if($_SESSION['page'] === 'index.php') echo 'style="background-color: #FFB700;"'; ?>><span class="material-symbols-outlined">chef_hat</span></div>Découvrir</li></a>
        <a href="mes-recettes.php"><li class="navbar-menu-button"><div class="navbar-menu-button-img" <?php if($_SESSION['page'] === 'mes-recettes.php') echo 'style="background-color: #FFB700;"'; ?>><span class="material-symbols-outlined">lunch_dining</span></div>Mes recettes</li></a>
        <a href="compte.php"><li class="navbar-menu-button"><li class="navbar-menu-button"><div class="navbar-menu-button-img" <?php if($_SESSION['page'] === 'compte.php') echo 'style="background-color: #FFB700;"'; ?>><span class="material-symbols-outlined">account_box</span></div>Mon compte</li></a>
        <li class="navbar-point"> </li>
    </ul>
</navbar>