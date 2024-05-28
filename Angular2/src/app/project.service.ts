import {Injectable} from '@angular/core';
import {Http, Response, Headers } from '@angular/http';
import { Router } from '@angular/router';
import { Broadcaster } from './tools/broadcaster';


import { Goal, Story, User, Comment, Category, UserGoal, Activity, IAction } from "./interface";

import {Observable}     from 'rxjs/Observable';
import 'rxjs/add/observable/throw';

// Operators
import 'rxjs/add/operator/catch';
import 'rxjs/add/operator/map';
import { environment } from '../environments/environment';


@Injectable()
export class ProjectService {

    private baseOrigin = environment.production?'https://www.bucketlist127.com':(<any>environment).stage? 'http://stage.bucketlist127.com':(<any>environment).test?'http://behat.bucketlist.loc':'http://bucketlist.loc';
    private angularOrigin = environment.production?'http://stage2.bucketlist127.com':'http://ang.bucketlist.loc';
    //private baseOrigin = 'http://stage.bucketlist127.com';

    private headers = new Headers();
    private appUser:User;
    private action: IAction = null;

    private envprefix = (environment.production || (<any>environment).stage)?'/':(<any>environment).test?'/':'/app_dev.php/';
    //private envprefix = '/';

    private baseUrl = this.baseOrigin + this.envprefix + 'api/v1.0/' ;
    private base2Url = this.baseOrigin + this.envprefix + 'api/v2.0/' ;
    private goalUrl =  this.baseUrl + 'goal/by-slug/';  // URL to web API
    private userUrl  = this.baseUrl + 'user';  // URL to web API
    private socialLoginUrl  = this.baseUrl + 'users/social-login/';  // URL to web API
    private registrationUrl  = this.baseUrl + 'users';  // URL to web API

    //modals
    private reportUrl = this.baseUrl + 'report';
    private commonUrl = '/common';
    private usersUrl = this.baseUrl + 'user-list/';
    // private friendsUrl = this.baseUrl + 'goals/';

    private userGoalsUrl = this.baseUrl + 'usergoals/';  // URL to web API
    private getStoryUrl = this.baseUrl + 'story/';  // URL to web API
    private addVoteUrl = this.baseUrl + 'success-story/add-vote/';  // URL to web API
    private removeVoteUrl = this.baseUrl + 'success-story/remove-vote/';  // URL to web API
    private removeStoryUrl = this.baseUrl + 'success-story/remove/';  // URL to web API
    private discoverGoalsUrl = this.baseUrl + 'goals/discover';  // URL to discover goal
    private baseStoryUrl = this.baseUrl + 'success-story/inspire';  // URL to discover goal
    private ideasUrl = this.baseUrl + 'goals/';  // URL to discover goal
    private activityUrl = this.base2Url + 'activities/';  // URL to activity
    private goalFriendsUrl = this.baseUrl + 'goal/random/friends'; //URL to get goalFriends
    private topIdeasUrl = this.baseUrl + 'top-ideas/1'; //URL to get top iteas
    private featuredIdeasUrl = this.baseUrl + 'goal/featured'; //URL to get featured iteas
    private badgesUrl = this.baseUrl + 'badges';
    private bottomMenuUrl = this.baseUrl + 'bottom/menu';
    private categoriesUrl = this.baseUrl + 'goal/categories';
    private notificationUrl = this.baseUrl + 'notifications';
    private notificationAllReadUrl = this.baseUrl + 'notification';
    private completeProfileUrl = this.baseUrl + 'user';
    private PageUrl = this.baseUrl + 'pages/';
    private sendEmailUrl = this.baseUrl + 'contact/send-email';
    private sendResettingEmailUrl = this.baseUrl + 'users/';
    private checkResetTokenUrl = this.baseUrl + 'user/check/reset-token/';
    private changePasswordUrl = this.baseUrl + 'users/news/passwords';
    private removeEmailUrl = this.baseUrl + 'settings/email';
    private changeSettingsUrl = this.baseUrl + 'user/update';
    private changeNotifySettingsUrl = this.baseUrl + 'notify-settings/update';
    private getNotifySettingsUrl = this.baseUrl + 'user/notify-settings';
    private activationAddedEmailUrl = this.baseUrl + 'user/activation-email/';
    private confirmRegUrl = this.baseUrl + 'user/confirm';
    private updateConfirmRegEmailUrl = this.baseUrl + 'user/update/activation-email';
    private checkConfirmRegEmailUrl = this.baseUrl + 'user/check/registration-token';

    //profile page urls
    private profileGoalsUrl = this.base2Url + 'usergoals/bucketlists?';
    private overallUrl = this.baseUrl + 'user/overall?';
    private followToggleUrl = this.baseUrl + 'users/';
    private followToggleUrl2 = '/toggles/followings';
    private calendarUrl = this.baseUrl + 'usergoal/calendar/data';

    private nearByUrl = this.baseUrl + 'goals/nearby/';
    private resetNearByUrl = this.baseOrigin + this.envprefix + 'usergoals/';
    private getCommentsUrl = this.baseUrl + 'comments/goal_';
    private putCommentUrl = this.baseUrl + 'comments/';
    private removeProfileUrl = this.baseUrl + 'user/delete/profile';
    private switchNotificationUrl = this.baseUrl + 'user/notify-settings/switch-off';
    private invisibleAllGoalsUrl = this.baseUrl + 'usergoals/invisible-all';

    constructor(private http:Http, private router:Router, private broadcaster: Broadcaster) {

        if(!environment.production)console.log('you are in development mode');
        this.headers.append('apikey', localStorage.getItem('apiKey'));
        this.broadcaster.on<User>('getUser')
            .subscribe(user => {
                this.appUser = user;
            });
    }

    initPaths(prefix: string){
        this.baseUrl = prefix + 'api/v1.0/' ;
        this.base2Url = prefix + 'api/v2.0/' ;
        this.goalUrl =  this.baseUrl + 'goal/by-slug/';  // URL to web API
        this.userUrl  = this.baseUrl + 'user';  // URL to web API
        this.socialLoginUrl  = this.baseUrl + 'users/social-login/';  // URL to web API
        this.registrationUrl  = this.baseUrl + 'users';  // URL to web API

        //modals
        this.reportUrl = this.baseUrl + 'report';
        this.commonUrl = '/common';
        this.usersUrl = this.baseUrl + 'user-list/';
        // this.friendsUrl = this.baseUrl + 'goals/';

        this.userGoalsUrl = this.baseUrl + 'usergoals/';  // URL to web API
        this.getStoryUrl = this.baseUrl + 'story/';  // URL to web API
        this.addVoteUrl = this.baseUrl + 'success-story/add-vote/';  // URL to web API
        this.removeVoteUrl = this.baseUrl + 'success-story/remove-vote/';  // URL to web API
        this.removeStoryUrl = this.baseUrl + 'success-story/remove/';  // URL to web API
        this.discoverGoalsUrl = this.baseUrl + 'goals/discover';  // URL to discover goal
        this.baseStoryUrl = this.baseUrl + 'success-story/inspire';  // URL to discover goal
        this.ideasUrl = this.baseUrl + 'goals/';  // URL to discover goal
        this.activityUrl = this.base2Url + 'activities/';  // URL to activity
        this.goalFriendsUrl = this.baseUrl + 'goal/random/friends'; //URL to get goalFriends
        this.topIdeasUrl = this.baseUrl + 'top-ideas/1'; //URL to get top iteas
        this.featuredIdeasUrl = this.baseUrl + 'goal/featured'; //URL to get featured iteas
        this.badgesUrl = this.baseUrl + 'badges';
        this.bottomMenuUrl = this.baseUrl + 'bottom/menu';
        this.categoriesUrl = this.baseUrl + 'goal/categories';
        this.notificationUrl = this.baseUrl + 'notifications';
        this.notificationAllReadUrl = this.baseUrl + 'notification';
        this.completeProfileUrl = this.baseUrl + 'user';
        this.PageUrl = this.baseUrl + 'pages/';
        this.sendEmailUrl = this.baseUrl + 'contact/send-email';
        this.sendResettingEmailUrl = this.baseUrl + 'users/';
        this.checkResetTokenUrl = this.baseUrl + 'user/check/reset-token/';
        this.changePasswordUrl = this.baseUrl + 'users/news/passwords';
        this.removeEmailUrl = this.baseUrl + 'settings/email';
        this.changeSettingsUrl = this.baseUrl + 'user/update';
        this.changeNotifySettingsUrl = this.baseUrl + 'notify-settings/update';
        this.getNotifySettingsUrl = this.baseUrl + 'user/notify-settings';
        this.activationAddedEmailUrl = this.baseUrl + 'user/activation-email/';
        this.confirmRegUrl = this.baseUrl + 'user/confirm';
        this.updateConfirmRegEmailUrl = this.baseUrl + 'user/update/activation-email';
        this.checkConfirmRegEmailUrl = this.baseUrl + 'user/check/registration-token';

        //profile page urls
        this.profileGoalsUrl = this.base2Url + 'usergoals/bucketlists?';
        this.overallUrl = this.baseUrl + 'user/overall?';
        this.followToggleUrl = this.baseUrl + 'users/';
        this.followToggleUrl2 = '/toggles/followings';
        this.calendarUrl = this.baseUrl + 'usergoal/calendar/data';

        this.nearByUrl = this.baseUrl + 'goals/nearby/';
        this.resetNearByUrl = prefix + 'usergoals/';
        this.getCommentsUrl = this.baseUrl + 'comments/goal_';
        this.putCommentUrl = this.baseUrl + 'comments/';
    }

    updateApiKeyInHeader() {
        this.headers.set('apikey', localStorage.getItem('apiKey'));
    }
    /**
     *
     * @param loginData
     * @returns {any}
     */
    auth(loginData: Object):Observable<any> {
        return this.http.post(this.baseUrl + 'users/logins', JSON.stringify(loginData)).map((res:Response) => res.json());
    }

    /**
     *
     * @param type
     * @param token
     * @param secret
     * @returns {Observable<R>}
     */
    socialLogin(type:string, token:string, secret?:string):Observable<any> {
        return this.http.get(this.socialLoginUrl + type + '/' + token + (secret?('/' + secret):'') + '?apikey=true')
            .map((r:Response) => r.json())
            .catch(this.handleError);
    }

    /**
     *
     * @returns {Observable<R>}
     */
    getPath(){
        return this.baseOrigin;
    }
    /**
     *
     * @returns {Observable<R>}
     */
    getAngularPath(){
        return this.angularOrigin;
    }

    /**
     *
     * @returns {Observable<R>}
     */
    getMyUser(){
        return this.appUser;
    }

    /**
     *
     * @param data
     */
    setMyUser(data){
        this.appUser = data;
    }

    setAction(action: IAction){
        this.action = action;
    }

    getAction():IAction{
        return this.action;
    }

    /**
     *
     * @param slug
     * @returns {Observable<R>}
     */
    getGoal(slug:string):Observable<any> {
        return this.http.get(this.goalUrl + slug, {headers: this.headers})
            .map((r:Response) => r.json())
            .catch(this.handleError);
    }

  /**
   *
   * @param badges
   * @returns {Observable<any>}
   */
    getMaxScore(badges: any):Observable<any> {

    return this.http.put(this.baseUrl+'badge/max/score', {'badges':badges},{headers: this.headers})
      .map((r:Response) => r.json())
      .catch(this.handleError);
    }

    /**
     *
     * @param id
     * @returns {Observable<R>}
     */
    getGoalMyId(id:number):Observable<any> {
        return this.http.get(this.ideasUrl + id, {headers: this.headers})
            .map((r:Response) => r.json())
            .catch(this.handleError);
    }

    /**
     *
     * @param id
     * @returns {Observable<R>}
     */
    createGoal(data, id?:number):Observable<any> {
        this.headers.set('apikey', localStorage.getItem('apiKey'));
        return this.http.post(this.ideasUrl + 'create' + (id?('/' + id):''), data, {headers: this.headers})
            .map((r:Response) => r.json())
            .catch(this.handleError);
    }

    /**
     *
     * @param data
     * @returns {Observable<R>}
     */
    sendConfirmRegistrationEmail(data:any):Observable<any> {
        this.headers.set('apikey', localStorage.getItem('apiKey'));
        return this.http.post(this.updateConfirmRegEmailUrl, data, {headers: this.headers})
            .map((r: Response) => r.json());
    }

    checkRegisterToken(data:any):Observable<any> {
        return this.http.post(this.checkConfirmRegEmailUrl, data, {headers: this.headers})
            .map((r: Response) => r.json());
    }

    /**
     *
     * @param start
     * @param count
     * @param userId
     * @param time
     * @returns {any}
     */
    getActivities(start:number, count:number, userId:number, time?:any):Observable<Activity[]> {
        return this.http.get(this.activityUrl + start + '/' + count + (userId?('/'+userId):'') +(time?('?time=' + time):''), {headers: this.headers})
            .map((r:Response) => r.json() as Activity[])
            .catch(this.handleError);
    }

    /**
     *
     * @param goalId
     * @param data
     * @returns {Observable<R>}
     */
    addUserGoal(goalId:number, data:any):Observable<UserGoal> {
        return this.http.put(this.userGoalsUrl + goalId, data, {headers: this.headers})
            .map((r:Response) => r.json() as UserGoal)
            .catch(this.handleError);
    }

    /**
     *
     * @param goalId
     * @param data
     * @returns {Observable<R>}
     */
    addUserGoalStory(goalId:number, data:any):Observable<any> {
        return this.http.put(this.ideasUrl + goalId + '/story', data, {headers: this.headers})
            .map((r:Response) => r.json())
            .catch(this.handleError);
    }

    /**
     *
     * @param goalId
     * @returns {Observable<R>}
     */
    removeUserGoal(goalId:number):Observable<UserGoal> {
        return this.http.delete(this.userGoalsUrl + goalId, {headers: this.headers})
            .map((r:Response) => r.json() as UserGoal)
            .catch(this.handleError);
    }

    /**
     *
     * @param goalId
     * @returns {Observable<R>}
     */
    getUserGoal(goalId:number):Observable<UserGoal> {
        return this.http.get(this.userGoalsUrl + goalId, {headers: this.headers})
            .map((r:Response) => r.json() as UserGoal)
            .catch(this.handleError);
    }

    /**
     *
     * @param goalId
     * @returns {Observable<R>}
     */
    setDoneUserGoal(goalId:number):Observable<UserGoal> {
        return this.http.get(this.userGoalsUrl + goalId + '/dones/true', {headers: this.headers})
            .map((r:Response) => r.json() as UserGoal)
            .catch(this.handleError);
    }

    /**
     *
     * @param goalId
     * @returns {Observable<R>}
     */
    getStory(goalId:number):Observable<any> {
        return this.http.get(this.getStoryUrl + goalId, {headers: this.headers})
            .map((r:Response) => r.json())
            .catch(this.handleError);
    }

    /**
     *
     * @param id
     * @returns {Observable<R>}
     */
    removeStory(id:number):Observable<any> {
        return this.http.delete(this.removeStoryUrl + id, {headers: this.headers})
            .map((r:Response) => r.json())
            .catch(this.handleError);
    }

    /**
     *
     * @param id
     * @returns {Observable<R>}
     */
    addVote(id:number):Observable<any> {
        return this.http.get(this.addVoteUrl + id, {headers: this.headers})
            .map((r:Response) => r.json())
            .catch(this.handleError);
    }

    /**
     *
     * @param id
     * @returns {Observable<R>}
     */
    removeVote(id:number):Observable<any> {
        return this.http.get(this.removeVoteUrl + id, {headers: this.headers})
            .map((r:Response) => r.json())
            .catch(this.handleError);
    }

    /**
     *
     */
    getUser():Observable<User> {
        this.headers.set('apikey', localStorage.getItem('apiKey'));
        return this.http.get(this.userUrl, {headers: this.headers})
            .map((r:Response) => r.json() as User)
            .catch(this.handleError);
    }

    /**
     *
     * @param uId
     * @returns {Observable<R>}
     */
    getUserByUId(uId:string):Observable<User> {
        let end = uId == 'my'?'':('/' + uId);
        return this.http.get(this.userUrl + end, {headers: this.headers})
            .map((r:Response) => r.json() as User)
            .catch(this.handleError);
    }

    /**
     *
     */
    getCompleteProfileUrl():Observable<any> {
        return this.http.get(this.completeProfileUrl, {headers: this.headers})
            .map((r:Response) => r.json())
            .catch(this.handleError);
    }

    /**
     *
     * @returns {Observable<T>}
     */
    getGaolFriends():Observable<any> {
        return this.http.get(this.goalFriendsUrl, {headers: this.headers})
            .map((r:Response) => r.json())
            .catch(this.handleError);
    }

    /**
     *
     * @param data
     * @returns {Observable<R>}
     */
    confirmUserRegistration(data:any):Observable<any> {
        return this.http.post(this.confirmRegUrl, data)
            .map((r: Response) => r.json());
    }

    /**
     *
     * @returns {Observable<T>}
     */
    getUserList(first:number, count:number, search:string, type:string):Observable<User[]> {
        return this.http.get(this.ideasUrl + first + '/friends/'+count+'?search='+search+'&type='+ type, {headers: this.headers})
            .map((r:Response) => r.json() as User[])
            .catch(this.handleError);
    }

    /**
     *
     * @returns {Observable<T>}
     */
    getTopIdeas():Observable<Goal[]> {
        return this.http.get(this.topIdeasUrl, {headers: this.headers})
            .map((r:Response) => r.json() as Goal[])
            .catch(this.handleError);
    }

    /**
     *
     * @returns {Observable<T>}
     */
    getFeaturedIdeas():Observable<Goal[]> {
        return this.http.get(this.featuredIdeasUrl, {headers: this.headers})
            .map((r:Response) => r.json() as Goal[])
            .catch(this.handleError);
    }

    /**
     *
     * @returns {Observable<T>}
     */
    getBadges():Observable<any> {
        return this.http.get(this.badgesUrl, {headers: this.headers})
            .map((r:Response) => r.json())
            .catch(this.handleError);
    }

    /**
     *
     * @returns {Observable<T>}
     */
    getNotifications(start: number, end: number):Observable<any> {
        return this.http.get(this.notificationUrl + '/' + start + '/' + end , {headers: this.headers})
            .map((r:Response) => r.json())
            .catch(this.handleError);
    }
    /**
     *
     * @returns {Observable<T>}
     */
    getNewNotifications(start: number, end: number, lastId: number):Observable<any> {
        return this.http.get(this.notificationUrl + '/' + start + '/' + end + '/' +lastId, {headers: this.headers})
            .map((r:Response) => r.json())
            .catch(this.handleError);
    }
    /**
     *
     * @returns {Observable<T>}
     */
    readAllNotifications():Observable<any>{
        return this.http.get(this.notificationAllReadUrl +'/all/read',{headers: this.headers})
            .catch(this.handleError);
    }
    /**
     *
     * @returns {Observable<T>}
     */
    deleteNotifications(id: number):Observable<any>{
        return this.http.delete(this.notificationUrl + '/' + id,{headers: this.headers})
            .catch(this.handleError);
    }

    /**
     *
     * @returns {Observable<T>}
     */
    deleteDrafts(id: number):Observable<any>{
        return this.http.delete(this.ideasUrl + id + '/drafts',{headers: this.headers})
            .catch(this.handleError);
    }

    /**
     *
     * @param type
     * @param start
     * @param count
     * @returns {Observable<R>}
     */
    getMyIdeas(type:string, start: number, count: number):Observable<any>{
        return this.http.get(this.ideasUrl + type + '/' + start +'/'+ count , {headers : this.headers})
            .map((r:Response) => r.json() as Goal[])
            .catch(this.handleError);
    }

    /**
     *
     * @returns {Observable<T>}
     */
    readSigle(id: number):Observable<any>{
        return this.http.get(this.notificationUrl + '/' +id +'/read',{headers: this.headers})
            .catch(this.handleError);
    }

    /**
     *
     * @returns {Observable<T>}
     */
    getleaderBoard(type:number, count:number):Observable<any> {
        return this.http.get(this.baseUrl + 'badges/' + type + '/topusers/' + count, {headers: this.headers})
            .map((r:Response) => r.json())
            .catch(this.handleError);
    }

    /**
     *
     * @returns {Observable<R>}
     */
    getDiscoverGoals():Observable<Goal[]> {
        // let params = new URLSearchParams();
        // params.set('action', 'opensearch');
        // params.set('format', 'json');
        // params.set('callback', 'JSONP_CALLBACK');

        return this.http.get(this.discoverGoalsUrl)
            .map((r:Response) => r.json() as Goal[])
            .catch(this.handleError);
    }
    /**
     *
     * @returns {Observable<R>}
     */
    getIdeaGoals(start:number, count:number, search:string = '',category:string = ''):Observable<Goal[]> {
        return this.http.get(this.ideasUrl + start + '/' + count + '?search=' + search + '&category=' + ((category && category != 'discover')?category:''), {headers: this.headers})
            .map((r:Response) => r.json() as Goal[])
            .catch(this.handleError);
    }

    /**
     *
     * @returns {Observable<R>}
     */
    getNearByGoals(latitude:number, longitude:number, start:number, count:number, isCompleted:boolean):Observable<Goal[]> {
        return this.http.get(this.nearByUrl + latitude + '/' + longitude + '/' + start + '/' + count + '/' + isCompleted, {headers: this.headers})
            .map((r:Response) => r.json() as Goal[])
            .catch(this.handleError);
    }

    /**
     *
     * @param goalId
     * @returns {Observable<R>}
     */
    resetNearByGoal(goalId:number):Observable<any> {
        return this.http.post(this.resetNearByUrl + goalId + '/toggles/interesteds', '', {headers: this.headers})
            .map((r:Response) => r.json())
            .catch(this.handleError);
    }

    /**
     *
     * @returns {Observable<R>}
     */
    getBaseStories():Observable<Story[]> {

        return this.http.get(this.baseStoryUrl)
            .map((r:Response) => r.json() as Story[])
            .catch(this.handleError);
    }

    /**
     *
     * @returns {Observable<R>}
     */
    getCategories():Observable<Category[]> {

        return this.http.get(this.categoriesUrl)
            .map((r:Response) => r.json() as Category[])
            .catch(this.handleError);
    }

    /**
     *
     * @returns {Observable<R>}
     */
    getBottomMenu():Observable<any> {

        return this.http.get(this.bottomMenuUrl)
            .map((r:Response) => r.json())
            .catch(this.handleError);
    }

    /**
     *
     * @param slug
     * @param locale
     * @returns {Observable<R>}
     */

    getPage(slug:string, locale:string):Observable<any> {

        return this.http.get(this.PageUrl + slug + '/' + locale)
            .map((r:Response) => r.json())
            .catch(this.handleError);
    }

    /**
     *
     * @returns {Observable<R>}
     */
    sendEmail(emailData: any):Observable<any> {
        return this.http.post(this.sendEmailUrl, {'emailData' : emailData})
            .map((r:Response) => r)
            .catch(this.handleError);
    }

    /**
     *
     * @param email
     * @returns {Observable<R>}
     */
    sendResettingEmail(email: any):Observable<any> {
        return this.http.get(this.sendResettingEmailUrl + email + '/reset')
            .map((r:Response) => r);
    }

    /**
     *
     * @param data
     * @returns {Observable<R>}
     */
    changePassword(data: any):Observable<any> {
        return this.http.post(this.changePasswordUrl, data, {headers: this.headers})
            .map((r: Response) => r.json());
    }

    /**
     *
     * @param token
     * @returns {Observable<R>}
     */
    checkResetToken(token: any):Observable<any> {
    return this.http.get(this.checkResetTokenUrl + token)
        .map((r: Response) => r.json());
    }

    /**
     *
     */
    getComments(slug:string):Observable<Comment[]> {
        return this.http.get(this.getCommentsUrl + slug, {headers: this.headers})
            .map((r:Response) => r.json())
            .catch(this.handleError);
    }
    /**
     *
     */
    putComment(id:number, body:any, commentId?:number):Observable<Comment> {
        let comment = commentId? ('/'+ commentId): '';
        return this.http.put(this.putCommentUrl + id + comment, {'commentBody': body}, {headers: this.headers})
            .map((r:Response) => r.json() as Comment)
            .catch(this.handleError);
    }

    /**
     *
     * @param regData
     * @returns {Observable<R>}
     */
    putUser(regData:any):Observable<any> {
        return this.http.post(this.registrationUrl, regData)
            .map((r:Response) => r.json())
    }

    /**
     *
     */
    getReport(data:any):Observable<any> {
        return this.http.get(this.reportUrl + '?commentId=' + data.contentId + '&type=' + data.contentType, {headers: this.headers})
            .map((r:Response) => r.json())
            .catch(this.handleError);
    }
    /**
     *
     */
    report(data:any):Observable<any> {
        return this.http.put(this.reportUrl, data, {headers: this.headers})
            .map((r:Response) => r.json())
            .catch(this.handleError);
    }

    /**
     *
     */
    getCommons(id:number, start?:number, count?:number):Observable<any> {
        this.headers.set('apikey', localStorage.getItem('apiKey'));
        let end = count?('/' + start + '/' + count):'';
        return this.http.get(this.ideasUrl + id + this.commonUrl + end, {headers: this.headers})
            .map((r:Response) => r.json())
            .catch(this.handleError);
    }

    /**
     *
     */
    getUsers(start:number, count:number, id:number, type:number):Observable<User[]> {
        return this.http.get(this.usersUrl + start + '/' + count + '/' + id + '/' + type, {headers: this.headers})
            .map((r:Response) => r.json() as User[])
            .catch(this.handleError);
    }




    //profile page requests
    /**
     *
     * @param id
     * @returns {Observable<R>}
     */
    toggleFollow(id:number):Observable<any> {
        return this.http.post(this.followToggleUrl + id + this.followToggleUrl2, {}, {headers: this.headers})
            .map((r:Response) => r.json())
            .catch(this.handleError);
    }

    /**
     *
     * @returns {Observable<R>}
     */
    getCalendarData():Observable<any> {
        return this.http.get(this.calendarUrl, {headers: this.headers})
            .map((r:Response) => r.json())
            .catch(this.handleError);
    }

    /**
     *
     * @param condition
     * @param count
     * @param first
     * @param isDream
     * @param notUrgentImportant
     * @param notUrgentNotImportant
     * @param urgentImportant
     * @param urgentNotImportant
     * @param status
     * @param userId
     * @returns {Observable<R>}
     */
    profileGoals(condition:number, count:number, first:number, isDream:boolean,
                 notUrgentImportant:boolean, notUrgentNotImportant:boolean,
                 urgentImportant:boolean, urgentNotImportant:boolean, status:string, userId?:number):Observable<any> {
        return this.http.get(this.profileGoalsUrl + 'condition=' + condition +
            '&count=' + count + '&first=' + first + '&isDream=' + isDream + '&notUrgentImportant=' + notUrgentImportant +
            '&notUrgentNotImportant=' + notUrgentNotImportant + '&urgentImportant=' + urgentImportant +
            '&status=' + status + '&urgentNotImportant=' + urgentNotImportant + '&userId=' + userId, {headers: this.headers})
            .map((r:Response) => r.json())
            .catch(this.handleError);
    }

    /**
     *
     * @param condition
     * @param count
     * @param first
     * @param isDream
     * @param notUrgentImportant
     * @param notUrgentNotImportant
     * @param urgentImportant
     * @param urgentNotImportant
     * @param status
     * @param userId
     * @param owned
     * @returns {Observable<R>}
     */
    getOverall(condition:number, count:number, first:number, isDream:boolean,
             notUrgentImportant:boolean, notUrgentNotImportant:boolean,
             urgentImportant:boolean, urgentNotImportant:boolean, status:string,
             userId?:number, owned?:boolean):Observable<any> {
        let path = owned?('owned=true'):('condition=' + condition +
        '&count=' + count + '&first=' + first + '&isDream=' + isDream + '&notUrgentImportant=' + notUrgentImportant +
        '&notUrgentNotImportant=' + notUrgentNotImportant + '&urgentImportant=' + urgentImportant +
        '&status=' + status + '&urgentNotImportant=' + urgentNotImportant + '&userId=' + userId);
        return this.http.get(this.overallUrl + path, {headers: this.headers})
            .map((r:Response) => r.json())
            .catch(this.handleError);
    }

    /**
     *
     * @param id
     * @param count
     * @param first
     * @returns {Observable<R>}
     */
    ownedGoals(id:number, count:number, first:number):Observable<any> {
        return this.http.get(this.ideasUrl + id + '/owned/' + first + '/' + count, {headers: this.headers})
            .map((r:Response) => r.json())
            .catch(this.handleError);
    }
    /**
     *
     * @param id
     * @param count
     * @param first
     * @returns {Observable<R>}
     */
    commonGoals(id:number, count:number, first:number):Observable<any> {
        return this.http.get(this.ideasUrl + id + '/common/' + first + '/' + count, {headers: this.headers})
            .map((r:Response) => r.json())
            .catch(this.handleError);
    }

    /**
     *
     * This service is used to remove user email
     *
     * @param email
     */
    removeUserEmail(email:string) {
        return this.http.delete(this.removeEmailUrl+'?email='+email, {headers: this.headers})
            .map((r:Response) => r.json())
            .catch(this.handleError);
    }

    /**
     *
     * This service is used to save user data
     *
     * @param data
     */
    saveUserData(data:any) {
        return this.http.post(this.changeSettingsUrl, {'bl_user_angular_settings':data}, {headers: this.headers})
            .map((r:Response) => r.json());
    }

    /**
     *
     * @param data
     * @returns {Observable<R>}
     */
    removeProfile(data){
        return this.http.put(this.removeProfileUrl, data, {headers: this.headers})
            .map((r:Response) => r.json())
            .catch(this.handleError);
    }

    /**
     *
     * @returns {Observable<R>}
     */
    switchNotificationsOff(){
        return this.http.get(this.switchNotificationUrl, {headers: this.headers})
            .map((r:Response) => r.json())
            .catch(this.handleError);
    }

    /**
     *
     * @returns {Observable<R>}
     */
    invisibleAllGoals(){
        return this.http.get(this.invisibleAllGoalsUrl, {headers: this.headers})
            .map((r:Response) => r.json())
            .catch(this.handleError);
    }

    /**
     *
     * @param secret
     * @param email
     * @returns {Observable<R>}
     */
    activationUserAddEmail(secret, email) {
        return this.http.get(this.activationAddedEmailUrl + secret +'/'+ email, {headers: this.headers})
            .map((r:Response) => r.json());
    }

    /**
     *
     * @param data
     * @returns {Observable<R>}
     */
    postNotifySettings(data:any) {
        return this.http.post(this.changeNotifySettingsUrl, {'bl_user_notify_angular_type':data}, {headers: this.headers})
            .map((r:Response) => r.json())
            .catch(this.handleError);
    }

    /**
     *
     * @returns {Observable<R>}
     */
    getNotifySettings() {
        return this.http.get(this.getNotifySettingsUrl, {headers: this.headers})
            .map((r:Response) => r.json())
            .catch(this.handleError);
    }

    /**
     *
     * @param error
     * @returns {ErrorObservable}
     */
    private handleError(error:any) {
        // In a real world app, we might use a remote logging infrastructure
        // We'd also dig deeper into the error to get a better message
        let errMsg = (error.message) ? error.message :
            error.status ? `${error.status} - ${error.statusText}` : 'Server error';
        console.error(errMsg); // log to console instead
        if(error.status && error.status == 401){
            localStorage.removeItem('apiKey');
            this.broadcaster.broadcast('logout', 'some message');
            this.router.navigate(['/']);
        }
        return Observable.throw(errMsg);
    }
}
