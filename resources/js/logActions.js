import { urls } from "@/config/urls";
import axios from "axios";

export class LogActions {
    
    constructor(user) {
        this.user = user;
    }

    save(url = window.location.href, method = 'GET', title = document.title, options = {}) {
        const form = new FormData();
        form.set('db', 'app-printers-consumables');        
        form.set('url', url);
        form.set('title', title);
        form.set('method', method);
        form.set('author', this.user.name);
        form.set('organization_code', this.user.org_code);
        Object.keys(options).forEach(key => {            
            form.set(`data[${key}]`, options[key]);
        });
        form.set('data[userAgent]', window.navigator.userAgent);

        axios.post(urls.logActions.save, form).then();
    }
}