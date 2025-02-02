<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>

<table class="custom-crm-tab-table" border="1">
    <thead>
        <tr>
            <th>ID записи</th>
            <th>Название</th>
            <th>Значение</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($arResult['DATA'] as $row): ?>
            <tr>
                <td><?= htmlspecialcharsbx($row['ID']) ?></td>
                <td><?= htmlspecialcharsbx($row['NAME']) ?></td>
                <td><?= htmlspecialcharsbx($row['VALUE']) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

