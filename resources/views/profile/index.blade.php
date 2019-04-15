@extends('layouts.base')

@section('title', 'プロフィール')
@section('column', 'two-column')

@include('layouts.head')
@include('layouts.header')

@section('content')
  <div class="prof-top">
    <div class="prof-top-user">
      <img src="{{ $user->profiles()->first()->img_filename ?? '/img/avatar/default.png' }}" alt="" class="prof-top-img">
      <p class="prof-top-user-name">
        {{ $user->name }}
      </p>
    </div>
    <div class="prof-top-nav-wrap">
      <nav class="prof-top-nav">
        <ul>
          <li>
            <a href="/profile?id={{ $user->id }}" class="prof-top-link {{ (empty(request()->isLikeShow)) ? 'prof-top-link-active' : '' }}">
              投稿<br>
                {{ $user->posts()->count() ?? 0 }}
            </a>
          </li>
          <li>
            <a href="/profile?isLikeShow=1&id={{ $user->id }}" class="prof-top-link {{ (!empty(request()->isLikeShow)) ? 'prof-top-link-active' : '' }}">
              いいね<br>
              {{ $user->likes()->count() ?? 0 }}
            </a>
          </li>
        </ul>
      </nav>
    </div>
  </div>

  <div class="content-wrap">

    <div class="sidebar">
      <div class="prof-side-content">
        <p class="prof-side-content-title">自己紹介</p>
        <p class="prof-side-content-main">
          {{ $user->profiles()->first()->bio ?? '' }}
        </p>
      </div>
      <div class="sidebar-line"></div>
      <div class="prof-side-content">
        <p class="prof-side-content-title">一番好きなツール</p>
        <p class="prof-side-content-main">
          {{ $user->profiles()->first()->like_tool ?? '' }}
        </p>
      </div>
        @if (Auth::check())
      <div class="sidebar-line"></div>
        @endif
      @if (Auth::check())
      <button class="prof-side-btn"><a href="/profile/edit">プロフィール編集</a></button>
      <button class="prof-side-btn"><a href="/user/pass_edit">パスワード変更</a></button>
      <button class="prof-side-btn"><a href="/user/withdraw">退会</a></button>
        @endif
    </div>

    <div class="post">
    @if(isset($posts))
        @foreach($posts as $post)
          @include('components.post', ['post' => $post])
        @endforeach
      @else
        @if ($isLikeShow)
          いいねをした投稿がありません。
        @else
          まだ投稿をしていません。
        @endif;
      @endif

    </div>

  </div>
@endsection

@include('layouts.footer')