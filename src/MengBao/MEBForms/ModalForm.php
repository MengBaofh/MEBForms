<?php

declare(strict_types=1);

namespace MengBao\MEBForms;

use pocketmine\form\FormValidationException;

/**
 * 确认窗口
 *
 * 只有两个按钮，回调收到true表示点了第一个按钮，false表示第二个。
 * 这种窗口没有关闭按钮，玩家按ESC等同于选了第二个按钮。
 */
class ModalForm extends BaseForm
{
    public function __construct(?\Closure $callable = null)
    {
        parent::__construct($callable);
        $this->data["content"] = "";
        //客户端要求这两个字段必须存在，先给默认值
        $this->data["button1"] = "gui.yes";
        $this->data["button2"] = "gui.no";
    }

    protected function getType(): string
    {
        return "modal";
    }

    public function setContent(string $content): void
    {
        $this->data["content"] = $content;
    }

    /**
     * 设置第一个按钮(确认)的文字
     */
    public function setButton1(string $text): void
    {
        $this->data["button1"] = $text;
    }

    /**
     * 设置第二个按钮(取消)的文字
     */
    public function setButton2(string $text): void
    {
        $this->data["button2"] = $text;
    }

    protected function processData(mixed &$data): void
    {
        if ($data === null) {
            return;
        }
        if (!is_bool($data)) {
            throw new FormValidationException("Expected bool response, got " . get_debug_type($data));
        }
    }
}