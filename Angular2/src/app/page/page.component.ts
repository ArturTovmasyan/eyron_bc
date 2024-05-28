import { Component, OnInit, OnDestroy } from '@angular/core';
import { ActivatedRoute, Router, NavigationEnd } from '@angular/router';
import { ValidationService } from '../validation.service';
import { FormBuilder, Validators } from '@angular/forms';
import { DomSanitizer } from '@angular/platform-browser'
import { MetadataService } from 'ng2-metadata';

import {ProjectService} from '../project.service';
import {CacheService, CacheStoragesEnum} from 'ng2-cache/ng2-cache';

@Component({
    selector: 'page',
    templateUrl: './page.component.html',
    styleUrls: ['./page.component.less']
})

export class PageComponent implements OnInit, OnDestroy {

    public eventId: number = 0;
    public name: string;
    public title: string;
    public isSend: boolean = false;
    public isDestroy: boolean = false;
    public description: any ;
    public data: any;
    public sub: any;
    public locale: string = 'en';
    public show: boolean = false;
    public emailData:any;

    constructor(
        private metadataService: MetadataService,
        private route: ActivatedRoute,
        private _projectService: ProjectService,
        private _cacheService: CacheService,
        private router:Router,
        private formBuilder: FormBuilder,
        private sanitizer: DomSanitizer
    ) {
        this.sub = router.events.subscribe((event) => {
            if(event instanceof NavigationEnd ) {
                if (!this.isDestroy && this.eventId != event.id) {
                    this.eventId = event.id;
                    window.scrollTo(0, 0);
                    this.name = this.route.snapshot.paramMap.has('name') ? this.route.snapshot.paramMap.get('name') : 'how-it-works';
                    if (this.name == 'contact-us') {
                        this.isSend = false;
                    }

                    this.getPage(this.name, this.locale);
                }
            }
        });

        this.emailData = this.formBuilder.group({
            'fullName': ['', Validators.required],
            'email': ['', [Validators.required, ValidationService.emailValidator]],
            'subject': ['', [Validators.required]],
            'message': ['', [Validators.required]],
        });
    }

    ngOnDestroy(){
        this.sub.unsubscribe();
        this.isDestroy = true;
    }

    ngOnInit() {
    }

    getPage(name, locale){

        this._projectService.getPage(name, locale)
            .subscribe(
                data => {
                    this.data = data[0];
                    if(this.name == 'about'){
                        this.description = this.sanitizer.bypassSecurityTrustHtml(this.data.description);
                    } else {
                        this.description = this.data.description;
                    }
                    this.title = this.data.title;
                    // this.metadataService.setTitle(this.title);
                    // this.metadataService.setTag('description', this.description);
                });
    }

    sendEmail(emailData) {

        this.show = true;

        this._projectService.sendEmail(emailData)
            .subscribe(
                () => {
                    this.isSend = true;
                    this.emailData.reset();
                    this.show = false;
                }
            );
    }
}