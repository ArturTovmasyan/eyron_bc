import {Story} from "./story";
import {Location} from "./location";

export interface Goal {
    id: number,
    title: string,
    description?: string,
    publish?: boolean,
    images?: any[],
    video_link?: any[],
    status?: boolean,
    language?: string,
    slug: string,
    stats?: any,
    distance?: any,
    location?: Location,
    is_my_goal?: number,
    success_stories?: Story[],
    cachedImage?: string,
    cached_image?: string
}
