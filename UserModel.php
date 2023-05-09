<?php
namespace voboghure\phpmvc;

use voboghure\phpmvc\db\DbModel;

abstract class UserModel extends DbModel {
	abstract public function getDisplayName(): string;
}
