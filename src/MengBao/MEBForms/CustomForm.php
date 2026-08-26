<?php

declare(strict_types=1);

namespace MengBao\MEBForms;

use pocketmine\form\FormValidationException;

/**
 * 自定义窗口
 *
 * 回调收到的是一个数组，下标就是元素的添加顺序(从0开始)。
 * 注意label也占一个下标，它的值恒为null，
 * 所以在label后面添加的元素下标要跟着往后数。
 * 玩家关闭窗口时收到null。
 */
class CustomForm extends BaseForm
{
    public function __construct(?\Closure $callable = null)
    {
        parent::__construct($callable);
        $this->data["content"] = [];
    }

    protected function getType(): string
    {
        return "custom_form";
    }

    /**
     * 纯文本说明，不产生输入值
     */
    public function addLabel(string $text): void
    {
        $this->data["content"][] = [
            "type" => "label",
            "text" => $text,
        ];
    }

    /**
     * 单行输入框，返回string
     */
    public function addInput(string $text, string $placeholder = "", string $default = ""): void
    {
        $this->data["content"][] = [
            "type" => "input",
            "text" => $text,
            "placeholder" => $placeholder,
            "default" => $default,
        ];
    }

    /**
     * 开关，返回bool
     */
    public function addToggle(string $text, bool $default = false): void
    {
        $this->data["content"][] = [
            "type" => "toggle",
            "text" => $text,
            "default" => $default,
        ];
    }

    /**
     * 滑块，返回float
     */
    public function addSlider(string $text, float $min, float $max, float $step = 1.0, ?float $default = null): void
    {
        $slider = [
            "type" => "slider",
            "text" => $text,
            "min" => $min,
            "max" => $max,
            "step" => max(0.1, $step),
        ];
        //默认值必须落在区间内，否则部分客户端会不显示这个滑块
        $slider["default"] = $default === null ? $min : min($max, max($min, $default));
        $this->data["content"][] = $slider;
    }

    /**
     * 下拉框，返回被选中项的索引(int)
     *
     * @param string[] $options
     */
    public function addDropdown(string $text, array $options, int $default = 0): void
    {
        $options = array_values(array_map('strval', $options));
        $this->data["content"][] = [
            "type" => "dropdown",
            "text" => $text,
            "options" => $options,
            "default" => $options === [] ? 0 : min(count($options) - 1, max(0, $default)),
        ];
    }

    /**
     * 分段滑块，返回被选中步骤的索引(int)
     *
     * @param string[] $steps
     */
    public function addStepSlider(string $text, array $steps, int $default = 0): void
    {
        $steps = array_values(array_map('strval', $steps));
        $this->data["content"][] = [
            "type" => "step_slider",
            "text" => $text,
            "steps" => $steps,
            "default" => $steps === [] ? 0 : min(count($steps) - 1, max(0, $default)),
        ];
    }

    public function getElementCount(): int
    {
        return count($this->data["content"]);
    }

    protected function processData(mixed &$data): void
    {
        if ($data === null) {
            return;
        }
        if (!is_array($data)) {
            throw new FormValidationException("Expected array response, got " . get_debug_type($data));
        }

        //客户端会为每个元素回一个值，label回的是null。
        //这里不强制校验个数：万一某个版本的客户端不回label，
        //抛异常会导致回调整个不执行，表单直接失效；
        //补齐到元素个数更稳，调用方按下标取值不会踩到未定义索引。
        $data = array_values($data);
        for ($i = count($data), $expected = count($this->data["content"]); $i < $expected; ++$i) {
            $data[$i] = null;
        }
    }
}