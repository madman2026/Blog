<?php

namespace Modules\User\Enums;

enum UserPermission: string
{
    case DashboardView = 'dashboard.view';
    case PostsViewAny = 'posts.view-any';
    case PostsCreate = 'posts.create';
    case PostsUpdateOwn = 'posts.update-own';
    case PostsUpdateAny = 'posts.update-any';
    case PostsDeleteOwn = 'posts.delete-own';
    case PostsDeleteAny = 'posts.delete-any';
    case PostsSubmit = 'posts.submit';
    case PostsReview = 'posts.review';
    case PostsPublish = 'posts.publish';
    case TaxonomiesManage = 'taxonomies.manage';
    case CommentsViewAny = 'comments.view-any';
    case CommentsModerate = 'comments.moderate';
    case CommentsDeleteAny = 'comments.delete-any';
    case UsersViewAny = 'users.view-any';
    case UsersUpdate = 'users.update';
    case UsersSuspend = 'users.suspend';
    case AuthorApplicationsReview = 'author-applications.review';
    case RolesManage = 'roles.manage';
}
