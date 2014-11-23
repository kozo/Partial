# PartialTrait

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
<?= $this->Partial('hoge'); ?>
```
