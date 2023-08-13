import React from 'react'
import HomepageReview from "./HomepageReview";


class HomepageReviews extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            'reviews': props.reviews || []
        };
    }

    createHomepageReview(review) {
        return <HomepageReview review={review} key={review.id}/>;
    }

    createHomepageReviews(reviews) {
        return reviews.map(this.createHomepageReview);
    }

    static getDerivedStateFromProps(props, state) {
        if (props.reviews !== state.reviews) {
            return {
                'reviews': props.reviews
            };
        }

        return null;
    }

    render() {
        return (
            <section key="homepage-reviews" className="page__section page__section--homepage-reviews">
                <div className="page__section__content">
                    <span className="page__section__subheading">5 z 5 na základě více než 100 recenzí</span>
                    <h2 className="page__section__heading">Hodnocení táborů</h2>

                    <div className="homepage-reviews-stars">
                        <img src="images/homepage/homepage-review-star.png" alt="*"/>
                        <img src="images/homepage/homepage-review-star.png" alt="*"/>
                        <img src="images/homepage/homepage-review-star.png" alt="*"/>
                        <img src="images/homepage/homepage-review-star.png" alt="*"/>
                        <img src="images/homepage/homepage-review-star.png" alt="*"/>
                    </div>
                    <div className="homepage-reviews">
                        {this.createHomepageReviews(this.state.reviews)}
                    </div>
                </div>
            </section>
        )
    }
};

export default HomepageReviews
