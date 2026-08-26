<?php

declare(strict_types=1);

namespace MengBao\MEBForms;

use pocketmine\form\FormValidationException;

/**
 * 按钮窗口
 *
 * 回调收到的是被点击按钮的索引；如果添加按钮时给了$label，
 * 收到的就是那个label字符串。玩家直接关闭窗口时收到null。
 */
class SimpleForm extends BaseForm
{
    /** @var array<int, string|int> 按钮索引 => 回调里要返回的值 */
    private array $labelMap = [];

    public function __construct(?\Closure $callable = null)
    {
        parent::__construct($callable);
        $this->data["content"] = "";
        $this->data["buttons"] = [];
    }

    protected function getType(): string
    {
        return "form";
    }

    public function setContent(string $content): void
    {
        $this->data["content"] = $content;
    }

    public function getContent(): string
    {
        return (string) $this->data["content"];
    }

    /**
     * 添加一个按钮
     *
     * @param string      $text      按钮文字，支持§颜色和\n换行
     * @param int         $imageType 0=材质路径, 1=网络url, -1=不要图标
     * @param string      $imagePath 图标地址，如 textures/ui/accept
     * @param string|null $label     指定后回调收到该字符串而不是按钮索引
     */
    public function addButton(string $text, int $imageType = -1, string $imagePath = "", ?string $label = null): void
    {
        $button = ["text" => $text];
        $image = $this->buildImage($imageType, $imagePath);
        if ($image !== null) {
            $button["image"] = $image;
        }
        $this->data["buttons"][] = $button;
        //没给label时用索引兜底，这样调用方按索引判断也能正常工作
        $this->labelMap[] = $label ?? count($this->labelMap);
    }

    public function getButtonCount(): int
    {
        return count($this->data["buttons"]);
    }

    protected function processData(mixed &$data): void
    {
        if ($data === null) {
            return;
        }
        if (!is_int($data)) {
            throw new FormValidationException("Expected int response, got " . get_debug_type($data));
        }
        if (!isset($this->labelMap[$data])) {
            throw new FormValidationException("Button index {$data} does not exist");
        }
        $data = $this->labelMap[$data];
    }
}