<?php

class OOP_Deactivator {

	public static function deactivate() {
        delete_option("onlyoffice-plugin-uuid");
	}

}