import { Component, OnInit, OnDestroy } from '@angular/core';
import { RouterModule, Routes, ActivatedRoute, Router, NavigationEnd } from '@angular/router';
import { ProjectService } from '../../project.service';
import { Broadcaster } from '../../tools/broadcaster';
import {CacheService} from 'ng2-cache/ng2-cache';

@Component({
  selector: 'app-registration-confirm',
  templateUrl: './registration-confirm.component.html',
  styleUrls: ['./registration-confirm.component.less']
})

export class RegistrationConfirmComponent implements OnInit, OnDestroy {

  eventId: number = 0;
  secret: any = null;
  sub: any;
  type: string;
  errorMessage:any;
  show:boolean = false;
  data:any = {};
  username:any;
  isDestroy: boolean = false;
  ready: boolean = false;
  appUser: any;

  constructor(private route: ActivatedRoute,
              private _projectService: ProjectService,
              private broadcaster: Broadcaster,
              private router:Router,
              private _cacheService: CacheService) {

      this.sub = router.events.subscribe((event) => {
          if(event instanceof NavigationEnd ) {
              if (!this.isDestroy && this.eventId != event.id) {

                  this.eventId = event.id;
                  this.secret = this.route.snapshot.paramMap.has('secret') ? this.route.snapshot.paramMap.get('secret') : null;

                  if (this.secret) {
                      this.confirmUserRegistration(this.secret);
                  }
              }
          }
        }
    );
  }

    ngOnInit(){
        this.broadcaster.broadcast('regConfirmMenu', false);
    }

    ngOnDestroy(){
        this.sub.unsubscribe();
        this.isDestroy = true;
    }

  /**
   *
   * @param secret
   */
  confirmUserRegistration(secret)
  {

    this.data['apikey'] = true;
    this.data['token'] = secret;

    this._projectService.confirmUserRegistration(this.data)
        .subscribe(
            (res) => {
              if(res.apiKey) {
                  localStorage.setItem('apiKey', res.apiKey);
                  this.broadcaster.broadcast('login', res.userInfo);
                  this.appUser = res.userInfo;
                  this.username =  this.appUser.username;
                  this._cacheService.set('user_',  this.appUser, {maxAge: 3 * 24 * 60 * 60});
                  this.broadcaster.broadcast('getUser',  this.appUser);
                  this.ready = true;
              }
            },
            error => {
              this.errorMessage = JSON.parse(error._body);

              if(this.errorMessage) {
                this.broadcaster.broadcast('error', this.errorMessage.user_confirm);
                this.router.navigate(['/error']);
              }
            }
        );
  }

}
