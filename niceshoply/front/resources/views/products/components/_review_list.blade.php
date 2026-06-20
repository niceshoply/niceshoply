@foreach($reviews as $review)
  <div class="review-item">
    <br/>
    <hr/>
    <div class="review-list row">
      <div class="row">
        <h5 class="col-2 mb-3">{{ $review->customer?->name ?? __('front/common.anonymous') }}</h5>
        <span class="col-4 text-left"><x-front-review :rating="$review->rating"/></span>
        <span class="col-6 text-end date">{{ $review->created_at }}</span>
      </div>
      <p class="mb-3">{{ $review['content'] }}</p>
      @if(!empty($review->images))
        <div class="review-images d-flex flex-wrap gap-2 mb-3">
          @foreach($review->images as $image)
            <a href="{{ $image }}" target="_blank" rel="noopener">
              <img src="{{ image_resize($image, 120, 120) }}" alt="review" class="rounded border" style="width:80px;height:80px;object-fit:cover;">
            </a>
          @endforeach
        </div>
      @endif
      @if($review->reply)
        <div class="alert alert-light border mb-3">
          <strong>{{ __('front/review.merchant_reply') }}</strong>
          <span class="text-muted small ms-2">{{ $review->reply_at }}</span>
          <p class="mb-0 mt-1">{{ $review->reply }}</p>
        </div>
      @endif
    </div>
  </div>
@endforeach
