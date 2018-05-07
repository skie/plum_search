<?php

$inputOptions = isset($inputOptions) ? $inputOptions : [];
$searchInputs = $this->Search->inputs($searchParameters, $inputOptions);
$formOptions = isset($formOptions) ? $formOptions : [];
if (empty($formOptions['id'])) {
    $formOptions['id'] = 'search-form';
}

?>
<div class="row">
    <?= $this->Form->create(null, $formOptions); ?>
    <?= $this->Form->controls($searchInputs, ['legend' => __('Search')]); ?>
    <?= $this->Form->button('Search', ['type' => 'submit', 'class' => 'btn btn-primary']); ?>
    <?= $this->Form->end(); ?>
</div>
