<?php

declare(strict_types=1);

namespace MengBao\MEBForms;

use pocketmine\plugin\PluginBase;

/**
 * MEBForms 主类
 *
 * 这个插件只提供表单类库，本身不注册指令也不监听事件。
 * 其它插件在plugin.yml里写 depend: [MEBForms]，
 * 就可以直接用 MengBao\MEBForms\SimpleForm 这些类。
 */
class Main extends PluginBase
{
    public function onEnable(): void
    {
        $this->getLogger()->info("§aMEBForms表单库已就绪 (v" . $this->getDescription()->getVersion() . ")");
    }
}