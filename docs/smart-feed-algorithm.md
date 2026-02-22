# The Smart Feed Algorithm Explained

A "Smart Feed" moves away from a simple chronological timeline and instead shows users the content most relevant to them. To achieve this, we calculate a **Feed Score** for each post.

---

## 1. Ranking Logic Overview
The ranking determines the order in which posts appear. It relies on a simple yet effective formula:
**`Feed Score = (Engagement Score × Personalization Multiplier) × Time Decay`**

Posts with the highest `Feed Score` appear at the top of the feed.

---

## 2. Engagement Scoring
Not all interactions are equal. We assign weights to different types of engagement to calculate a base metric of how interesting a post is:
*   **Likes**: Weight = 1 (Low effort)
*   **Comments**: Weight = 3 (Medium effort, encourages discussion)
*   **Shares**: Weight = 5 (High effort, strong endorsement)

**Formula**: 
`Engagement Score = (Likes × 1) + (Comments × 3) + (Shares × 5)`

---

## 3. Time Decay (Gravity)
We don't want a highly engaging post from 3 years ago to stay at the top forever. Time decay reduces a post's score as it ages. We can use a HackerNews-style gravity formula:

**Formula**: 
`Time Decay Multiplier = 1 / ( (Age_in_hours + 2)^Gravity )`

*   *Gravity* is a tunable constant (e.g., `1.8`). A higher gravity makes the feed refresh faster by penalizing older posts more aggressively.

---

## 4. Personalization (Affinity Scoring)
Users should see more content from friends they actually interact with often. We introduce an affinity multiplier:
*   **Base Friend**: `1.0`
*   **Close Friend** (frequent DMs, likes, profile visits): `1.5`
*   **New Connection**: `1.2` (boost new friends temporarily)

---

## 5. Query Optimization & Indexing Strategies
Calculating this score on-the-fly across millions of rows using an `ORDER BY` clause will crash your database.

*   **Time-Bounded Candidate Generation**: Instead of scoring the entire database, we only query "Candidate Posts" (e.g., posts created by friends in the last 72 hours). We calculate the score *only* on this much smaller subset.
*   **Denormalization**: Do not use `COUNT()` joins for likes and comments during feed generation. Store `likes_count`, `comments_count`, and `shares_count` directly on the `posts` table and update them asynchronously.
*   **Indexing**: A composite index on `(user_id, created_at)` is absolutely critical for rapidly fetching the candidate posts of a user's friends.

---

# Step-by-Step Implementation Plan

### Phase 1: Database & Data Structure Updates
1.  **Add Counter Columns**: Add `likes_count`, `comments_count`, and `shares_count` to the `posts` table.
2.  **Add Indexes**: Ensure `posts` has a composite B-Tree index on `(user_id, created_at)`.
3.  **Create Affinities (Optional later)**: Create a `user_affinities` table `(user_id, target_user_id, score)` to track how much User A interacts with User B.

### Phase 2: The Core SQL / Formula (Realtime Batching)
*   When User A requests their feed, fetch the IDs of their friends.
*   Query the `posts` table where `user_id IN (friends)` AND `created_at >= NOW() - 3 days`.
*   Apply the ranking formula using SQL mathematics:
    ```sql
    SELECT *,
    ( 
      ( (likes_count * 1) + (comments_count * 3) + (shares_count * 5) + 1 ) 
      / POWER( TIMESTAMPDIFF(HOUR, created_at, NOW()) + 2, 1.8 ) 
    ) as feed_score
    FROM posts ...
    ORDER BY feed_score DESC
    ```

### Phase 3: Pagination Efficiency
*   **The Problem with Offset/Limit**: If scores update while a user is scrolling, posts will shift, causing duplicates or missing posts.
*   **The Solution**: When a user loads the feed, generate the top 100 post IDs based on the algorithm and store that array temporarily in **Redis** (e.g., key `feed:user_id:session_id`). 
*   Pagination is then handled by slicing that Redis list (e.g., `0-15`, `16-30`) and pulling the actual post data by ID from the database using standard Cursor pagination.

### Phase 4: Caching & Precomputed Feeds (Fan-out strategy)
As the platform scales, calculating the feed on-the-fly—even with time bounds—becomes too slow.
*   **Hybrid Fan-Out On Write**: When User A publishes a post, push that `post_id` directly into the Redis Feed List (`ZSET`) of all their active friends.
*   Background workers asynchronously update the scores in the Redis `ZSETs` periodically as engagement happens or time passes.

---

# 📋 Recommended Prioritization of Tasks

1.  **Task 1 (Foundation)**: Add engagement counter columns to the `posts` table and ensure they update incrementally when interactions happen.
2.  **Task 2 (Candidate Selection)**: Write the repository method to fetch a subset of posts (last X days from friends).
3.  **Task 3 (Scoring Algorithm)**: Implement the mathematical scoring formula inside the database query to sort the candidate posts.
4.  **Task 4 (Pagination)**: Build the mechanism to paginate the scored results dynamically without breaking user scrolling.
5.  **Task 5 (Scaling - Later)**: Introduce Redis for the Fan-out write model and cursor caching.
