<?php
namespace voboghure\phpmvc\form;

use voboghure\phpmvc\Model;

abstract class BaseField {
	public Model $model;
	public string $attribute;

	public function __construct( Model $model, string $attribute ) {
		$this->model     = $model;
		$this->attribute = $attribute;
	}

	abstract public function renderInput(): string;

	public function __toString() {
		return sprintf( '
			<div class="mb-3">
				<label for="%s" class="form-label">%s</label>
				%s
				<div class="invalid-feedback">%s</div>
			</div>
		',
			$this->attribute,
			$this->model->getLabels( $this->attribute ),
			$this->renderInput(),
			$this->model->getFirstError( $this->attribute )
		);
	}
}
