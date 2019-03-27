<?php
/**
 * @var $list array
 * @var $level integer
 * @var $class string
 * @var $pTitle string
 */

use yii\bootstrap\Html;
use yii\helpers\Url;

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

            <a href="<?= Url::toRoute(['auth-manager/item-edit', 'pid' => $item['id'], 'level' => $item['level'] + 1, 'parent_title' => $item['description']]) ?>"
               data-toggle='modal' data-target='#ajaxModal'>
                <i class="fa fa-plus-circle"></i>
            </a>
        </td>
        <td><?= $item['name'] ?></td>
        <td style="padding: 3px">
            <?= Html::textInput('sort', $item['sort'], ['class' => 'form-control input-sm', 'data-id' => $item['id']]); ?>
        </td>
        <td style="padding: 3px">
            <a href="<?= Url::toRoute(['auth-manager/item-edit', 'pid' => $item['pid'], 'level' => $item['level'], 'name' => $item['name'], 'parent_title' => $pTitle]) ?>"
               data-toggle='modal' data-target='#ajaxModal' class="btn btn-default btn-sm" title="编辑">
                <i class="fa fa-edit"></i>
            </a>
            <a href="<?= Url::toRoute(['auth-manager/item-del', 'name' => $item['name']]) ?>"
               onclick="return confirm('确认删除吗？');return false;" class="btn btn-default btn-sm" title="删除">
                <i class="fa fa-times"></i>
            </a>
        </td>
    </tr>

    <?php if (!empty($item['son'])) {
        echo $this->render('_item-tree', [
            'list' => $item['son'],
            'pTitle' => $item['description'],
            'class' => 'r' . $item['id'] . ' ' . $class
        ]);
    } ?>
<?php endforeach; ?>
