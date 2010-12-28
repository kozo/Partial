<?php
$params = array();
if (!empty($cache)) {
	$params += compact('cache');
}
echo $this->Partial->render($element, $params);