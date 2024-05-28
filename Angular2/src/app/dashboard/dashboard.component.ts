import { Component, OnDestroy } from '@angular/core';
import { Router, NavigationEnd, ActivatedRoute } from '@angular/router';
import { Broadcaster } from '../tools/broadcaster';
import { ProjectService } from '../project.service';

@Component({
  selector: 'app-dashboard',
  templateUrl: './dashboard.component.html',
  styleUrls: ['./dashboard.component.css']
})
export class DashboardComponent implements OnDestroy {

  sub: any;

  constructor(
      private router: Router,
      private route: ActivatedRoute,
      private _projectService: ProjectService,
      private broadcaster: Broadcaster
  ) {
    this.sub = router.events.subscribe((event) => {
        if(event instanceof NavigationEnd ) {
          if (event.url.indexOf('/login') != -1) {
            if (!localStorage.getItem('apiKey')) {
              this.broadcaster.broadcast('openLogin', 'some message');
            }
            if(this.route.snapshot.paramMap.has('type') && this.route.snapshot.paramMap.has('id')) {
                this._projectService.setAction({
                  id: this.route.snapshot.paramMap.get('id'),
                  type: this.route.snapshot.paramMap.get('type'),
                  slug: this.route.snapshot.paramMap.get('slug')
                });
              if (localStorage.getItem('apiKey')) {
                this.broadcaster.broadcast('someAction');
              }
            }

          }

          if (localStorage.getItem('apiKey')) {
            this.router.navigate(['/activity']);
          }

          window.scrollTo(0,0);
        }
      });
  }

  ngOnDestroy(){
    this.sub.unsubscribe();
  }
}
