<?php

namespace common\widgets\kindeditor;

use Yii;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\widgets\InputWidget;

/**
 * Class Kindeditor
 * @package phpsdks\kindeditor
 */
class KindeditorWidget extends InputWidget
{
    //配置选项，参阅KindEditor官网文档(定制菜单等)
    public $clientOptions = [];

    /**
     * 定义编辑器的类型
     * 默认为textEditor;
     * uploadButton：自定义上传按钮
     * dialog:弹窗
     * colorpicker:取色器
     * file-manager浏览服务器
     * image-dialog 上传图片
     * multiImageDialog批量上传图片
     * fileDialog 文件上传
     */
    public $editorType;

    /**
     * @var array 自定义配置
     */
    public $params = [
        'remark' => ''
    ];

    //默认配置
    protected $_options;

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        $this->id = $this->hasModel() ? Html::getInputId($this->model, $this->attribute) : $this->id;
        $this->_options = [
            'fileManagerJson' => Url::to(['/utils/kindeditor', 'action' => 'fileManagerJson']),
            'uploadJson' => Url::to(['/utils/kindeditor', 'action' => 'uploadJson']),
            'width' => '100%',
            'height' => '480',
            'allowImageUpload' => true,
            'allowFileManager' => true,
            'allowFlashUpload' => false,
            'allowMediaUpload' => false,
            'allowFileUpload' => false,
            // 'langType' => (strtolower(Yii::$app->language) == 'en-us') ? 'en' : 'zh_CN', //kindeditor支持一下语言：en,zh_CN,zh_TW,ko,ar
        ];

        $this->clientOptions = ArrayHelper::merge($this->_options, $this->clientOptions);
        if (Yii::$app->getRequest()->enableCsrfValidation) {
            $this->clientOptions['extraFileUploadParams'] = ArrayHelper::merge(
                isset($this->clientOptions['extraFileUploadParams']) ? $this->clientOptions['extraFileUploadParams'] : [],
                [Yii::$app->getRequest()->csrfParam => Yii::$app->getRequest()->csrfToken]
            );
        }
        if ($this->hasModel()) {
            parent::init();
        }
    }

    public function run()
    {
        $this->registerClientScript();
        if ($this->hasModel()) {
            switch ($this->editorType) {
                case 'uploadButton':
                    return Html::activeInput('text', $this->model, $this->attribute, ['id' => $this->id, 'readonly' => "readonly"]) . '<input type="button" id="uploadButton" value="Upload" />';
                    break;
                case 'colorpicker':
                    return Html::activeInput('text', $this->model, $this->attribute, ['id' => $this->id]) . '<input type="button" id="colorpicker" value="打开取色器" />';
                    break;
                case 'file-manager':
                    return Html::activeInput('text', $this->model, $this->attribute, ['id' => $this->id]) . '<input type="button" id="filemanager" value="浏览服务器" />';
                    break;
                case 'file-dialog':
                    return Html::activeInput('text', $this->model, $this->attribute, ['id' => $this->id]) . '<input type="button" id="insertfile" value="选择文件" />';
                    break;
                case 'image-dialog':
                    $input = Html::activeInput('text', $this->model, $this->attribute, ['id' => $this->id, 'class' => 'form-control', 'autocomplete' => 'off']);
                    $value = Html::getAttributeValue($this->model, $this->attribute);
                    // $errorImg = Url::to(['/static/img/noPic_192x192.png']);
                    $errorImg = '/jimiyh/jmyh/public/'.\Yii::getAlias('@static/img/noPic_192x192.png');
                    $tpl = <<<EOT
						<div class="input-group">
							{$input}
							<span class="input-group-btn">
								<button class="btn btn-default" type="button" id="{$this->id}-add">选择图片</button>
							</span>
						</div>
						<div class="input-group" style="margin-top: 5px;">
							<img id="{$this->id}-img" src="{$value}" onerror="this.src='{$errorImg}'; this.title='图片未找到.'" class="img-responsive img-thumbnail" width="150" />
							<em id="{$this->id}-del" class="close" style="position:absolute; top: 0; right: -14px;" title="删除这张图片" onclick="deleteImage(this)">×</em>
						</div>
						<span class="text-muted">{$this->params['remark']}</span>
EOT;
                    return $tpl;
                    break;
                default:
                    return Html::activeTextarea($this->model, $this->attribute, ['id' => $this->id, 'class' => 'form-control']);
                    break;
            }
        } else {
            switch ($this->editorType) {
                case 'uploadButton':
                    return Html::input('text', $this->id, $this->value, ['id' => $this->id, 'readonly' => "readonly"]) . '<input type="button" id="uploadButton" value="Upload" />';
                    break;
                case 'colorpicker':
                    return Html::input('text', $this->id, $this->value, ['id' => $this->id]) . '<input type="button" id="colorpicker" value="打开取色器" />';
                    break;
                case 'file-manager':
                    return Html::input('text', $this->id, $this->value, ['id' => $this->id]) . '<input type="button" id="filemanager" value="浏览服务器" />';
                    break;
                case 'file-dialog':
                    return Html::input('text', $this->id, $this->value, ['id' => $this->id]) . '<input type="button" id="insertfile" value="选择文件" />';
                    break;
                case 'image-dialog':
                    $this->name = !empty($this->name) ? $this->name : $this->id;
                    $input = Html::input('text', $this->name, $this->value, ['id' => $this->id, 'class' => 'form-control', 'autocomplete' => 'off']);
                    // $errorImg = Url::to(['/static/img/noPic_192x192.png']);
                    $errorImg = \Yii::getAlias('@static/img/noPic_192x192.png');
                    $tpl = <<<EOT
						<div class="input-group">
							{$input}
							<span class="input-group-btn">
								<button class="btn btn-default" type="button" id="{$this->id}-add">选择图片</button>
							</span>
						</div>
						<div class="input-group" style="margin-top: 5px;">
							<img id="{$this->id}-img" src="{$this->value}" onerror="this.src='{$errorImg}'; this.title='图片未找到.'" class="img-responsive img-thumbnail" width="150" />
							<em id="{$this->id}-del" class="close" style="position:absolute; top: 0; right: -14px;" title="删除这张图片" onclick="deleteImage(this)">×</em>
						</div>
						<span class="text-muted">{$this->params['remark']}</span>
EOT;
                    return $tpl;
                    break;
                default:
                    return Html::textarea($this->id, $this->value, ['id' => $this->id]);
                    break;
            }
        }
    }

    /**
     * 注册客户端脚本
     */
    protected function registerClientScript()
    {
        KindeditorAsset::register($this->view);
        $clientOptions = json_encode($this->clientOptions);
        $fileManagerJson = $this->clientOptions['fileManagerJson'];
        $uploadJson = $this->clientOptions['uploadJson'];
        $extraFileUploadParams = json_encode($this->clientOptions['extraFileUploadParams']);

        switch ($this->editorType) {
            case 'uploadButton':
                $script = <<<EOT
					$(function (){
                    var uploadButton = KindEditor.uploadbutton({
                        button: $('#uploadButton')[0],
                        fieldName: 'imgFile',
                        url: '{$uploadJson}',
                        afterUpload: function(data) {
                            if (data.error === 0) {
                                var url =KindEditor.formatUrl(data.url, 'absolute');
                                $('#{$this->id}').val(url);
                            } else {
                                alert(data.message);
                            }
                        },
                        afterError: function(str) {
                            alert('自定义错误信息: ' + str);
                        }
                    });
                    uploadbutton.fileBox.change(function(e) {
                        uploadbutton.submit();
                    });
                })
EOT;
                break;
            case 'colorpicker':
                $script = <<<EOT
					KindEditor.ready(function(K) {
						var colorpicker;
						K('#colorpicker').bind('click', function(e) {
							e.stopPropagation();
							if (colorpicker) {
								colorpicker.remove();
								colorpicker = null;
								return;
							}
							var colorpickerPos = K('#colorpicker').pos();
							colorpicker = K.colorpicker({
								x : colorpickerPos.x,
								y : colorpickerPos.y + K('#colorpicker').height(),
								z : 19811214,
								selectedColor : 'default',
								noColor : '无颜色',
								click : function(color) {
									K('#{$this->id}').val(color);
									colorpicker.remove();
									colorpicker = null;
								}
							});
						});
						K(document).click(function() {
							if (colorpicker) {
								colorpicker.remove();
								colorpicker = null;
							}
						});
					});
EOT;
                break;
            case 'file-manager':
                $script = <<<EOT
					KindEditor.ready(function(K) {
						var editor = K.editor({
							fileManagerJson : '{$fileManagerJson}'
						});
						K('#filemanager').click(function() {
							editor.loadPlugin('filemanager', function() {
								editor.plugin.filemanagerDialog({
									viewType : 'VIEW',
									dirName : 'image',
									clickFn : function(url, title) {
										K('#{$this->id}').val(url);
										editor.hideDialog();
									}
								});
							});
						});
					});
EOT;
                break;
            case 'file-dialog':
                $script = <<<EOT
					KindEditor.ready(function(K) {
						var editor = K.editor({
							allowFileManager: true,
							"uploadJson": "{$uploadJson}",
							"fileManagerJson": "{$fileManagerJson}",
						});
						K('#insertfile').click(function() {
							editor.loadPlugin('insertfile', function() {
								editor.plugin.fileDialog({
									fileUrl: K('#{$this->id}').val(),
									clickFn: function(url, title) {
										K('#{$this->id}').val(url);
										editor.hideDialog();
									}
								});
							});
						});
					});
EOT;
                break;
            case 'image-dialog':
                $script = <<<EOT
					$(function() {
						var editor = KindEditor.editor({
							allowFileManager: true,
							"uploadJson": "{$uploadJson}",
							"fileManagerJson": "{$fileManagerJson}",
							"extraFileUploadParams": {$extraFileUploadParams},
						});
						$('#{$this->id}-add').click(function() {
							editor.loadPlugin('image', function() {
								editor.plugin.imageDialog({
									imageUrl: $('#{$this->id}').val(),
									clickFn: function(url, title, width, height, border, align) {
						                $('#{$this->id}').val(url);
						                $('#{$this->id}-img').get(0).src = url;
										editor.hideDialog();
									}
								});
							});
						});
						
						$('#{$this->id}-del').click(function() {
							$('#{$this->id}').val('');
							$('#{$this->id}-img').get(0).src = './static/img/noPic_192x192.png';
						});
					});
EOT;
                break;
            default:
                $script = <<<EOT
				$(function (){
					KindEditor.create("#{$this->id}", {$clientOptions});
				});
EOT;
                break;
        }
        $this->view->registerJs($script);
    }
}
