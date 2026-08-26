# MEBForms
A lightweight form library for PocketMine-MP 5

[English](#english) · [中文](#中文)

| | |
|---|---|
| API | `5.0.0` |
| Load | `STARTUP` |
| Dependencies | None |
| Author | MengBao |

## English
MEBForms is a **library-only plugin** that wraps PocketMine-MP's native `Form` interface into three ready-to-use form classes. It registers no commands and listens for no events. Let your plugin open a GUI window in a few lines of code.

No DEVirion, no libasynql, no Composer. `load: STARTUP` makes sure it loads before regular plugins, so anything depending on it always finds the classes.

### Why another form library

Older libraries like FormAPI mostly stopped at API 3.x/4.x, and on PM5 they either error out or behave oddly. MEBForms is written directly against PM5's `pocketmine\form\Form` while keeping FormAPI's calling conventions (`addButton`, `addInput`, `imageType = -1` for no icon), so porting an old plugin needs almost no rethinking.

It also handles a few things you actually run into:

- **Closing the window with ESC** consistently passes `null` to your callback. No silently dropped events, no exceptions.
- **Slider defaults** are clamped into `min`–`max`. An out-of-range default makes some clients skip rendering the slider entirely, which is a painful bug to track down.
- **Dropdown / step slider default indices** are clamped to a valid range.
- **Custom form responses are padded** to the element count. Some client versions don't send a value for `label`, and strictly validating the count would stop the whole callback from running, making the form look like it "does nothing when clicked". With padding, indexing by position never hits an undefined index.
- Unexpected data throws `FormValidationException`, which the server logs instead of crashing.

### The three form classes

| Class | UI | Value passed to the callback |
|---|---|---|
| `SimpleForm` | A column of buttons, icons supported | Button index `int`, or the `label` string you specified |
| `CustomForm` | Input / toggle / slider / dropdown / step slider | Array, indexed by the order elements were added |
| `ModalForm` | Two-button confirmation dialog | `true` = first button, `false` = second |

All extend `BaseForm` and share `setTitle()` and `setCallable()`.

### Installation

Drop `MEBForms` into `plugins/`, then declare the dependency in your own plugin's `plugin.yml`:

```yaml
depend: [MEBForms]
```

### Usage

Button window:

```php
use MengBao\MEBForms\SimpleForm;

$form = new SimpleForm(function(Player $player, $data): void {
    if ($data === null) {
        return; //player closed the window
    }
    match ($data) {
        "shop"  => $player->sendMessage("Opening shop"),
        "spawn" => $player->sendMessage("Teleporting to spawn"),
        default => null,
    };
});
$form->setTitle("§lMain Menu");
$form->setContent("Pick an option");
$form->addButton("Shop", 0, "textures/items/emerald", "shop");
$form->addButton("Back to spawn", -1, "", "spawn");
$player->sendForm($form);
```

Once you pass `addButton`'s fourth argument `label`, the callback receives that string instead of an index, so inserting a button in the middle doesn't force you to renumber the `match` arms. Without a `label` the callback receives the index, and matching on indices works just as well.

Custom window:

```php
use MengBao\MEBForms\CustomForm;

$form = new CustomForm(function(Player $player, $data): void {
    if ($data === null) {
        return;
    }
    //note: a label takes an index too and is always null, so later elements shift down
    $name  = (string) $data[1];
    $isPvp = (bool)   $data[2];
    $size  = (float)  $data[3];
    $mode  = (int)    $data[4]; //index of the selected option
});
$form->setTitle("Create claim");
$form->addLabel("Fill in the claim details");
$form->addInput("Claim name", "16 characters max");
$form->addToggle("Allow PVP", false);
$form->addSlider("Radius", 8, 128, 8, 32);
$form->addDropdown("Type", ["Residential", "Commercial", "Farm"]);
$player->sendForm($form);
```

Confirmation dialog:

```php
use MengBao\MEBForms\ModalForm;

$form = new ModalForm(function(Player $player, bool $confirm): void {
    if ($confirm) {
        $player->sendMessage("Deleted");
    }
});
$form->setTitle("Confirm deletion");
$form->setContent("This cannot be undone. Are you sure?");
$form->setButton1("§cDelete");
$form->setButton2("Cancel");
$player->sendForm($form);
```

A modal form has no close button — pressing ESC is the same as clicking the second button, so a `ModalForm` callback can type its parameter as `bool` directly.

### Notes

- Button text and content both support `§` color codes and `\n` line breaks.
- Icon parameter: `0` = texture path (e.g. `textures/ui/accept`), `1` = web URL, `-1` = no icon.
- Use `setCallable()` to reuse one form instance with a different callback.
- `getButtonCount()` / `getElementCount()` are handy for keeping indices straight when building forms dynamically.

---

## 中文

MEBForms 是一个**纯类库插件**，把 PocketMine-MP 的原生 `Form` 接口包装成三个开箱即用的表单类。它自己不注册任何指令、不监听任何事件，让你的插件用几行代码弹出 GUI 窗口。

不需要 DEVirion，不需要 libasynql，不需要 Composer。`load: STARTUP` 保证它先于普通插件加载，依赖它的插件不会拿不到类。

### 为什么再做一个表单库

FormAPI 这类老库大多停留在 API 3.x/4.x，在 PM5 上要么报错要么行为诡异。MEBForms 直接按 PM5 的 `pocketmine\form\Form` 写，同时保留了 FormAPI 的调用习惯（`addButton`、`addInput`、`imageType = -1` 表示无图标），从老插件迁过来几乎不用改思路。

除此之外还处理了几个实际会踩到的坑：

- **玩家按 ESC 关闭窗口**统一给回调传 `null`，不会静默丢事件，也不会抛异常。
- **滑块默认值**自动夹到 `min`~`max` 区间内。默认值越界时部分客户端会直接不渲染这个滑块，这个 bug 很难查。
- **下拉框 / 分段滑块的默认索引**自动夹到合法范围。
- **自定义表单的应答长度**会补齐到元素个数。某些客户端版本不为 `label` 回值，硬校验个数会让整个回调不执行、表单变成"点了没反应"；补齐之后按下标取值不会撞到未定义索引。
- 数据不符合预期时抛 `FormValidationException`，由核心记录日志，不会崩服。

### 三个表单类

| 类 | 界面 | 回调收到的值 |
|---|---|---|
| `SimpleForm` | 一列按钮，支持图标 | 按钮索引 `int`，或你指定的 `label` 字符串 |
| `CustomForm` | 输入框 / 开关 / 滑块 / 下拉框 / 分段滑块 | 数组，下标 = 元素添加顺序 |
| `ModalForm` | 两个按钮的确认框 | `true` = 第一个按钮，`false` = 第二个 |

全部继承自 `BaseForm`，共用 `setTitle()`、`setCallable()`。

### 安装

把 `MEBForms` 放进 `plugins/`，然后在你自己插件的 `plugin.yml` 里声明依赖：

```yaml
depend: [MEBForms]
```

### 用法

按钮窗口：

```php
use MengBao\MEBForms\SimpleForm;

$form = new SimpleForm(function(Player $player, $data): void {
    if ($data === null) {
        return; //玩家关掉了窗口
    }
    match ($data) {
        "shop"  => $player->sendMessage("打开商店"),
        "spawn" => $player->sendMessage("传送到出生点"),
        default => null,
    };
});
$form->setTitle("§l主菜单");
$form->setContent("选一个功能");
$form->addButton("商店", 0, "textures/items/emerald", "shop");
$form->addButton("回出生点", -1, "", "spawn");
$player->sendForm($form);
```

`addButton` 的第四个参数 `label` 给了之后，回调收到的就是这个字符串而不是索引——中间插一个按钮也不用回去改 `match` 的数字。不给 `label` 时回调收到索引，按索引判断照样能用。

自定义窗口：

```php
use MengBao\MEBForms\CustomForm;

$form = new CustomForm(function(Player $player, $data): void {
    if ($data === null) {
        return;
    }
    //注意 label 也占一个下标，值恒为 null，后面的元素要跟着往后数
    $name  = (string) $data[1];
    $isPvp = (bool)   $data[2];
    $size  = (float)  $data[3];
    $mode  = (int)    $data[4]; //选中项的索引
});
$form->setTitle("创建领地");
$form->addLabel("填写领地信息");
$form->addInput("领地名称", "最多16个字");
$form->addToggle("允许PVP", false);
$form->addSlider("半径", 8, 128, 8, 32);
$form->addDropdown("类型", ["住宅", "商业", "农场"]);
$player->sendForm($form);
```

确认框：

```php
use MengBao\MEBForms\ModalForm;

$form = new ModalForm(function(Player $player, bool $confirm): void {
    if ($confirm) {
        $player->sendMessage("已删除");
    }
});
$form->setTitle("确认删除");
$form->setContent("这个操作不可撤销，确定吗？");
$form->setButton1("§c确定删除");
$form->setButton2("取消");
$player->sendForm($form);
```

确认框没有关闭按钮，玩家按 ESC 等同于点了第二个按钮，所以 `ModalForm` 的回调可以直接把参数标成 `bool`。

### 提示

- 按钮文字和内容都支持 `§` 颜色码和 `\n` 换行。
- 图标参数：`0` = 材质路径（如 `textures/ui/accept`），`1` = 网络 URL，`-1` = 不要图标。
- 想复用同一个表单实例又要换回调，用 `setCallable()`。
- `getButtonCount()` / `getElementCount()` 在动态生成表单时用来对下标很方便。

---

