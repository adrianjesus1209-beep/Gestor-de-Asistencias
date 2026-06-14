<!-- Fernando Ruiz. 31.083.595 -->
<div class="nav-item dropdown">
    <button class="btn dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
        Profesores
    </button>
    <ul class="dropdown-menu">
        <li><a class="dropdown-item" href="index.php?route=manage-actives-list">Activos</a></li>
        <li><a class="dropdown-item" href="index.php?route=manage-inactives-list">Inactivos</a></li>
        <li><a class="dropdown-item" href="index.php?route=dashboard">Volver al Dashboard</a></li>
    </ul>
</div>

<div class="card Lista">
    <div class="card-header Lista_Titulo">
        Lista de Profesores (Activos)
    </div>
    <table class="table table-striped campos">
        <thead>
            <tr>
                <th scope="col"> </th>
                <th scope="col">Nombre</th>
                <th scope="col">Cedula</th>
                <th scope="col">Estado</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center"><input class="form-check-input" type="checkbox" value="" id="check1"></td>
                <td>Profesor promedio 1</td>
                <td>31083595</td>
                <td>Activo</td>
            </tr>
            <tr>
                <td class="text-center"><input class="form-check-input" type="checkbox" value="" id="check2"></td>
                <td>Profesor promedio 2</td>
                <td>31083595</td>
                <td>Activo</td>
            </tr>
            <tr>
                <td class="text-center"><input class="form-check-input" type="checkbox" value="" id="check3"></td>
                <td>Profesor promedio 3</td>
                <td>31083595</td>
                <td>Activo</td>
            </tr>
        </tbody>
    </table>

    <button type="submit" class="btn cambio1">Desactivar Profesor</button>

<nav aria-label="...">
    <ul class="pagination pagination-sm">
        <li class="page-item active">
            <a class="page-link" aria-current="page">1</a>
        </li>
        <li class="page-item"><a class="page-link" href="index.php?route=manage-actives-list">2</a></li>
        <li class="page-item"><a class="page-link" href="index.php?route=manage-actives-list">3</a></li>
    </ul>
</nav>