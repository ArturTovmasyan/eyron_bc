import { Component, OnInit , ViewEncapsulation, ViewChild, ElementRef, Renderer, OnDestroy } from '@angular/core';
import { RouterModule, Routes, ActivatedRoute, Router, NavigationEnd } from '@angular/router';
import { Broadcaster } from '../tools/broadcaster';
import { MetadataService } from 'ng2-metadata';
import { Angulartics2 } from 'angulartics2';

import {Goal} from '../interface/goal';
import {Marker} from '../interface/marker';
import {Location} from '../interface/location';
import {Category} from '../interface/category';
import {ProjectService} from '../project.service';
import {CacheService, CacheStoragesEnum} from 'ng2-cache/ng2-cache';



@Component({
  selector: 'app-ideas',
  templateUrl: './ideas.component.html',
  styleUrls: ['./ideas.component.less'],
  encapsulation: ViewEncapsulation.None
})
export class IdeasComponent implements OnInit, OnDestroy {
    @ViewChild("tooltip")
    public tooltipElementRef: ElementRef;
    public isMobile=(window.innerWidth<768);
    public sub: any;
    public category: string;
    public errorMessage: string;
    public filterVisibility: boolean = false;
    public eventId: number = 0;
    public isHover: boolean = false;
    public ideasTitle: boolean = true;
    public isDestroy: boolean = false;
    public noIdeas: boolean = false;
    public hoveredText: string = '';
    public serverPath:string = '';
    
    public start: number = 0;
    public count: number = 7;
    public latitude: number;
    public longitude: number;
    public userLocation: any;
    public isCompletedGoals: boolean = false;
    public search: string = '';
    public sliderCount: number;
    public searchError: string = '';
    public locations:Location[] = [];
    public locationsIds = [];
    
    public categories: Category[];
    public ideas: Goal[];
    public reserve: Goal[];
    public config: Object;
    constructor(
      private angulartics2: Angulartics2,
      private metadataService: MetadataService,
      private route: ActivatedRoute,
      private _projectService: ProjectService,
      private _cacheService: CacheService,
      private broadcaster: Broadcaster,
      private router:Router,
      public renderer: Renderer
    ) {
      this.sub = router.events.subscribe((event) => {
          if(event instanceof NavigationEnd ) {
              if (!this.isDestroy && this.eventId != event.id) {
                  this.eventId = event.id;
                  window.scrollTo(0, 0);
                  this.start = 0;
                  this.locationsIds = [];
                  this.locations = [];
                  this.category = this.route.snapshot.paramMap.has('category') ? this.route.snapshot.paramMap.get('category') : 'discover';
                  this.search = this.route.snapshot.paramMap.has('search') ? this.route.snapshot.paramMap.get('search') : '';
                  this.metadataService.setTitle('Ideas');
                  this.metadataService.setTag('description', 'Ideas for ' + this.category);
                  this.ideas = null;
                  this.reserve = null;
                  this.ideasTitle = false;
                  this.getGoals();
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
        let data = this._cacheService.get('categories');
        if(data){
          this.categories = data;
          this.initSlide();
        }else {
          this.getCategories();
        }
    
        this.search = this.route.snapshot.paramMap.has('search')?this.route.snapshot.paramMap.get('search'):'';
          
        this.broadcaster.on<Marker>('location_changed')
              .subscribe(marker => {
                  this.latitude = marker.latitude;
                  this.longitude = marker.longitude;
                  this.userLocation = {
                      latitude: this.latitude,
                      longitude: this.longitude
                  };
                  this.locationsIds = [];
                  this.locations = [];
                  this.start = 0;
                  this.ideas = null;
                  this.reserve = null;
                  this.getNearByGoals();
        });
    }
    
    initSlide(){
        if(window.innerWidth < 766){
            this.sliderCount = 4;
            //$scope.isMobile = true;
            //$scope.placeholder = '';
        }
        else if(window.innerWidth < 992){
            this.sliderCount = (this.categories.length < 7)?this.categories.length + 1 : 7;
            //$scope.isMobile = false;
            //$scope.placeholder = $scope.placeholderText;
        }
        else {
            this.sliderCount = (this.categories.length < 10)?this.categories.length + 1 : 10;
            //$scope.isMobile = false;
            //$scope.placeholder = $scope.placeholderText;
        }

        this.config  = {
            observer: true,
            autoHeight: true,
            slidesPerView: this.sliderCount,
            nextButton: '.swiper-button-next',
            prevButton: '.swiper-button-prev',
            spaceBetween: 0
        };
        this.filterVisibility = true;
    }

    getCategories(){
    this._projectService.getCategories()
        .subscribe(
            categories => {
              this.categories = categories;
              this.initSlide();
              this._cacheService.set('categories', categories, {maxAge: 3 * 24 * 60 * 60});
            },
            error => this.errorMessage = <any>error);
  }

    getGoals(){
    if(this.category == 'nearby')return;

    this._projectService.getIdeaGoals(this.start, this.count, this.search, this.category)
        .subscribe(
            goals => {
              this.noIdeas = (this.noIdeas && this.search.length == 0 && this.category == 'discover') || (!goals || !goals.length);
                if(this.noIdeas && (this.search.length > 0 || this.category != 'discover')){
                    this.searchError = this.search;
                    this.search = '';
                    setTimeout(() => {
                        this.category = 'discover';
                        this.noIdeas = false;
                        this.getGoals();
                    },1300);

                } else{
                    this.ideas = goals;
                    this.start += this.count;
                    this.setReserve();
                }
            },
            error => this.errorMessage = <any>error);
  }

    setReserve(){
        let category = this.category;
        if(this.category == 'nearby'){
          this._projectService.getNearByGoals(this.latitude, this.longitude, this.start, this.count, this.isCompletedGoals)
              .subscribe(
                  goals => {
                      //when change category before request
                      if(category != this.category)return;
                      
                      this.reserve = goals;
                      this.optimizeReserveImages();
                      this.start += this.count;
                  },
                  error => this.errorMessage = <any>error);
        } else {
          this._projectService.getIdeaGoals(this.start, this.count, this.search, this.category)
              .subscribe(
                  goals => {
                      //when change category before request
                      if(category != this.category)return;
                      
                      this.reserve = goals;
                      this.optimizeReserveImages();
                      this.start += this.count;
                  },
                  error => this.errorMessage = <any>error);
        }
  }

    doSearch(){
      this.ideasTitle = false;
      if(this.category == 'nearby'){
          this.category = 'discover'
      }
      this.router.navigate(['/ideas/'+this.category + '/' + this.search]);
  }

    getReserve(){
        this.broadcaster.broadcast('ideaShowMore');
        this.angulartics2.eventTrack.next({ action: 'Load more in select category', properties: { category: 'Goal', label: 'Load more in category ' + this.category + ' from angular2'}});
        this.ideas = this.ideas.concat(this.reserve);
          if(this.category == 'nearby'){
              this.calculateLocations(this.reserve);
          }
        this.setReserve();
    }

    getNearByGoals(){
        this._projectService.getNearByGoals(this.latitude, this.longitude, this.start, this.count, this.isCompletedGoals)
          .subscribe(
              goals => {
                  this.ideas = goals;
                  this.start += this.count;
                  this.calculateLocations(this.ideas);
                  this.setReserve();
              },
              error => this.errorMessage = <any>error);
    }

    completedChange(){
      if(this.latitude && this.longitude){
          this.start = 0;
          this.getNearByGoals();
      }
    }
    
    calculateLocations(items){
       for(let item of items){
           let location:any = {
               id: 0,
               latitude: 0,
               lat: 0,
               longitude: 0,
               lng: 0,
               slug: '',
               title: '',
               status: 0,
               isHover: false,
           };
           if(item.location && this.locationsIds.indexOf(item.id) == -1){
               location.id = item.id;
               this.locationsIds.push(location.id);
               location.latitude = item.location.latitude;
               location.lat = item.location.latitude;
               location.longitude = item.location.longitude;
               location.lng = item.location.longitude;
               location.title = item.title;
               location.slug = item.slug;
               location.status = item.is_my_goal;
               this.locations.push(location);
           }
       }
       this.broadcaster.broadcast('getLocation', this.locations);
  }

    optimizeReserveImages(){
       for(let item of this.reserve){
           let img;
           if(item.cached_image){
               img = new Image();
               img.src = item.cached_image;
           }
       }
   }

    hideJoin(event){
        if(event && event.val){
            this.hoveredText = event.val;
            this.isHover = true;
            let left = +event.ev.pageX - 60;
            let top  = event.ev.pageY - 60;
            this.renderer.setElementStyle(this.tooltipElementRef.nativeElement, 'left', left + 'px');
            this.renderer.setElementStyle(this.tooltipElementRef.nativeElement, 'top', top + 'px');

        } else {
            this.hoveredText = '';
            this.isHover = false
        }

    }
}
