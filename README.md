# PartialTrait

Partial Plugin is CakePHP element of small scope.

## Installation

1. composer.json
```
"kozo/partial": "3.0.*@dev"
```
2. Run `composer update`


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
