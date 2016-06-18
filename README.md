# PartialTrait

Partial Plugin is CakePHP element of small scope.

## Installation

```
composer require kozo/partial:"~3.0"
```


## Usage

AppView.php
```
use Partial\View\PartialTrait;

class AppView extends View {
	use PartialTrait;
}
```

_hoge.ctp
```
Partial content.
```

example.ctp
```
<?= $this->partial('hoge'); ?>
```
