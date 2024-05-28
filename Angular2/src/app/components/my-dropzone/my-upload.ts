import { UploadItem }    from 'angular2-http-file-upload';

export class MyUploadItem extends UploadItem {
    constructor(file: any, my_url: string) {
        super();
        let key = localStorage.getItem('apiKey');
        this.url = my_url;
        this.headers = { 'apikey': key};
        this.file = file;
    }
}
