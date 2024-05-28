/**
 * Created by gevor on 2/18/17.
 */
import { OnInit, OnDestroy } from '@angular/core';
import { ProjectService} from '../project.service';
import { User} from '../interface/user';
import { ActivatedRoute, Router, NavigationEnd } from '@angular/router';
import { MetadataService } from 'ng2-metadata';
import { CacheService} from 'ng2-cache/ng2-cache';

export class Leaderboard implements OnInit, OnDestroy {

    public data:any;
    public sub: any;
    public appUser:User;
    public type:number = 1;
    public category:string;
    public isDestroy: boolean = false;
    public categories = ['','traveler', 'mentor', 'innovator'];
    public count:number = 10;
    public eventId:number = 0;
    public errorMessage:string;
    public serverPath:string = '';
    public isMobile = (window.innerWidth < 768);
    public isTouchdevice = (window.innerWidth > 600 && window.innerWidth < 992);

    constructor(
        protected metadataService: MetadataService,
        protected _projectService: ProjectService,
        protected router:Router,
        protected _cacheService: CacheService,
        protected route: ActivatedRoute
    ) {
        this.sub = router.events.subscribe((event) => {
            if(event instanceof NavigationEnd ) {
                if (!this.isDestroy && this.eventId != event.id) {
                    this.eventId = event.id;
                    window.scrollTo(0, 0);
                    this.category = this.route.snapshot.paramMap.has('type') ? this.route.snapshot.paramMap.get('type') : 'innovator';
                    this.metadataService.setTitle('Leaderboard');
                    this.metadataService.setTag('description', 'Leaderboard for ' + this.category);
                    this.type = this.categories.indexOf(this.category);
                    this.getleaderBoard();
                }
            }
        })
    }

    ngOnDestroy(){
        this.sub.unsubscribe();
        this.isDestroy = true;
    }

    ngOnInit() {
        this.serverPath = this._projectService.getPath();
        this.appUser = this._cacheService.get('user_');
    }

    getleaderBoard() {
        this._projectService.getleaderBoard(this.type, this.count)
            .subscribe(
                data => {
                    this.data = data;
                },
                error => this.errorMessage = <any>error);
    }

    getFullName(user) {
        let name = user.first_name + ' ' + user.last_name,
            count = this.isTouchdevice?50:((this.isMobile || (window.innerWidth > 991 && window.innerWidth < 1170))?8:24);
        return (name.length > count)?(name.substr(0,count -3) + '...'):name;
    };
}