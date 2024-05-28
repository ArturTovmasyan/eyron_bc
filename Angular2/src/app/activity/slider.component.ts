import { Component, OnInit, Input } from '@angular/core';
import { Goal } from '../interface/goal';
import { Activity } from '../interface/activity';
import { Broadcaster } from '../tools/broadcaster';
declare var Swiper;
@Component({
    selector: 'my-slider',
    templateUrl: './my-slider.component.html'
})
export class SliderComponent implements OnInit {
    @Input() reserveGoals: Goal[];
    @Input() activity: Activity;
    @Input() index: number;
    activeIndex:number = 1;
    name:string = '#activity-slider';
    // config: Object = {
    //     observer: true,
    //     observeParents: true,
    //     autoHeight: true,
    //     onSlideNextStart: (ev) =>{
    //         this.activeIndex++;
    //         this.broadcaster.broadcast('slide-change', {id:this.activity.id, index: this.activeIndex, number: this.index});
    //         this.loadImage();
    //         ev.update(true);
    //     },
    //     onSlidePrevStart: (ev) => {
    //         this.activeIndex--;
    //         this.broadcaster.broadcast('slide-change', {id:this.activity.id, index: this.activeIndex, number: this.index});
    //     },
    //
    //     // loop: true,
    //     nextButton: '.swiper-button-next',
    //     prevButton: '.swiper-button-prev',
    //     spaceBetween: 30
    // };

    constructor(private broadcaster: Broadcaster) {}

    ngOnInit() {
        // setTimeout(()=>{
            // if(this.slide && this.slide.Swiper){
            //     this.slide.Swiper.update(true)
            // }
        // },15000);
        setTimeout(()=>{
            if(!this.reserveGoals || this.reserveGoals.length < 2)return;
            this.name = this.name + '-'+this.index+'-on';
            let activity_swiper = new Swiper(this.name, {
                observer: true,
                autoHeight: true,
                onSlideNextStart: (ev)=> {
                    this.activeIndex++;
                    this.broadcaster.broadcast('slide-change', {id:this.activity.id, index: this.activeIndex, number: this.index});
                    this.loadImage();
                    ev.update(true);

                },
                onSlidePrevStart: (ev) => {
                    this.activeIndex--;
                    this.broadcaster.broadcast('slide-change', {id:this.activity.id, index: this.activeIndex, number: this.index});
                },

                // loop: true,
                nextButton: '.swiper-button-next',
                prevButton: '.swiper-button-prev',
                spaceBetween: 30
            })
        },2000)
    }

    loadImage(){
        if(!this.reserveGoals[this.activeIndex] && this.activity.goals[this.activeIndex]){
            this.reserveGoals.push(this.activity.goals[this.activeIndex]);
        }
    }

}
