import React from 'react'


const HomepageReview = (props) => {
    return (
        <div key={props.review.id} className={'homepage-review homepage-review' + props.review.styleId}>
            <div className="homepage-review-content-wrapper">
                <div className="homepage-review-content">
                    {props.review.content}
                </div>
            </div>
            <div className="homepage-review-author">
                <img src={props.review.author.avatar} className="homepage-review-author-avatar" alt={props.review.author.name} loading="lazy" />
                <div>
                    <span className="homepage-review-author-name">{props.review.author.name}</span>
                    <span className="homepage-review-author-date">{props.review.date}</span>
                </div>
            </div>
        </div>
    )
};

export default HomepageReview
