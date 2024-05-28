import { Component, OnInit, Input, Output, EventEmitter , ViewEncapsulation, OnChanges } from '@angular/core';
import { ProjectService } from '../../../project.service';
import {CacheService, CacheStoragesEnum} from 'ng2-cache/ng2-cache';
import { Broadcaster } from '../../../tools/broadcaster';
import { Uploader }      from 'angular2-http-file-upload';
import { ProfileHeader }  from '../profile-header';
import {MdSnackBar, MdSnackBarConfig} from '@angular/material';
import { TranslateService} from 'ng2-translate';
import { Router } from '@angular/router';

import {User} from "../../../interface/user";

@Component({
  selector: 'profile-header',
  templateUrl: './api.profile-header.component.html',
  styleUrls: ['./profile-header.component.less'],
  encapsulation: ViewEncapsulation.None
})
export class ProfileHeaderComponent extends ProfileHeader{

    constructor(
        protected broadcaster: Broadcaster,
        protected _projectService: ProjectService,
        protected _cacheService: CacheService,
        protected uploaderService: Uploader,
        protected snackBar: MdSnackBar,
        protected _translate: TranslateService,
        protected router: Router
    )
    {
        super(broadcaster, _projectService, _cacheService, uploaderService,snackBar,_translate, router);
    }

}
