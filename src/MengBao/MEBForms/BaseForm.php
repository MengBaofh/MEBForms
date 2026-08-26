<?php

declare(strict_types=1);

namespace MengBao\MEBForms;

use pocketmine\form\Form;
use pocketmine\form\FormValidationException;
use pocketmine\player\Player;

/**
 * 表单基类
 *
 * 负责三件事：保存回调、组装客户端要的json、把客户端的应答交给回调。
 * 玩家关闭表单时客户端只发一个取消原因，核心会把应答变成null，
 * 这里统一把null透传给回调，让调用方自己决定怎么处理。
 */
abstract class BaseForm implements Form
{
    /** @var array<string, mixed> 发给客户端的表单数据 */
    protected array $data = [];

    /** @phpstan-var (\Closure(Player, mixed): void)|null */
    protected ?\Closure $callable;

    /**
     * @param \Closure|null $callable 玩家提交或关闭表单后的回调，签名为 function(Player $player, $data): void
     */
    public function __construct(?\Closure $callable = null)
    {
        $this->callable = $callable;
    }

    public function getCallable(): ?\Closure
    {
        return $this->callable;
    }

    public function setCallable(?\Closure $callable): void
    {
        $this->callable = $callable;
    }

    /**
     * 表单类型，由子类给出(form/custom_form/modal)
     */
    abstract protected function getType(): string;

    public function setTitle(string $title): void
    {
        $this->data["title"] = $title;
    }

    public function getTitle(): string
    {
        return (string) ($this->data["title"] ?? "");
    }

    final public function handleResponse(Player $player, $data): void
    {
        $this->processData($data);
        if ($this->callable !== null) {
            ($this->callable)($player, $data);
        }
    }

    /**
     * 子类在这里把客户端的原始应答加工成好用的形式
     *
     * @param mixed $data
     * @throws FormValidationException 数据不符合预期时抛出，核心会记录日志而不是崩服
     */
    protected function processData(mixed &$data): void
    {
        //默认不加工
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        $data = $this->data;
        $data["type"] = $this->getType();
        return $data;
    }

    /**
     * 组装按钮/元素的图标结构
     *
     * @return array<string, string>|null
     */
    protected function buildImage(int $imageType, string $imagePath): ?array
    {
        //-1表示不要图标，和FormAPI的约定保持一致
        if ($imageType === -1 || $imagePath === "") {
            return null;
        }
        return [
            "type" => $imageType === 0 ? "path" : "url",
            "data" => $imagePath,
        ];
    }
}