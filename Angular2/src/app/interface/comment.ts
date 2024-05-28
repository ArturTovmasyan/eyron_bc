import {User} from "./user";

export interface Comment {
    id: number,
    children?: any,
    comment_body: string,
    created_at?: string,
    updated_at?: string
    author: User,
    visible?: boolean,
    reply?: boolean
}

