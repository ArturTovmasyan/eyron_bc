import { Injectable } from '@angular/core';
import { Router, CanActivate } from '@angular/router';


@Injectable()
export class LoginGuard implements CanActivate {
    constructor(
        private router: Router
    ) {

    }

    canActivate() {
        if (!localStorage.getItem('apiKey')) {
            return true;
        }

        this.router.navigate(['/activity']);
    }
}
