<?php

class OOP_Activator {

	public static function activate() {
        add_option("onlyoffice-plugin-uuid", wp_generate_uuid4());
	}

}