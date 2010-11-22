<?php

abstract class EcSite {

    static function getActiveUser() {
        
        if (user::isAuthenticated()) {
            return (user::getActiveUser());
        
        
        } else {
        
        
        
        }
        
    }
    
    static function getCategories() {

        return ProductCategory(0);    
    
    }


}

