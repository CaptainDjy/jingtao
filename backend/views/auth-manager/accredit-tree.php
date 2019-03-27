<?php
/**
 * @var $list array
 * @var $level integer
 * @var $class string
 * @var $pTitle string
 */


?>

<?php foreach ($list as $k => $item): ?>
    <tr class="<?= $class ?>" data-id="<?= $item['id'] ?>">
        <td>
            <?php if (!empty($item['son'])): ?>
                <div class="fa fa-minus-square fold" style="cursor:pointer;color: #666;"></div>
            <?php endif; ?>
        </td>

        <td>
            <?php for ($i = 1; $i < $item['level']; $i++): ?>
                &nbsp;&nbsp;&nbsp;&nbsp;
            <?php endfor; ?>
            <?php if ($item['pid'] > 0): ?>
                <?= isset($list[$k + 1]) ? '├──' : '└──' ?>
            <?php endif; ?>
            <?= $item['description'] ?>
            <input type="checkbox" class="check_list<?php echo $item['level'] ?>">

        </td>
        <td><?= $item['name'] ?></td>

    </tr>
    <?php if (!empty($item['son'])) {
        echo $this->render('accredit-tree', [
            'list' => $item['son'],
            'pTitle' => $item['description'],
            'class' => 'r' . $item['id'] . ' ' . $class
        ]);
    } ?>
<?php endforeach; ?>
