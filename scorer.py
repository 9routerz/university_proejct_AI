from llm import ask_claude
from tools import get_history, get_session_meta


def score_session(session_id: str) -> dict:
    """
    Send the full session history to the LLM and get back a 0-100 score with feedback.
    Returns dict with keys: score (int), feedback (str), breakdown (str)
    """
    history = get_history(session_id)
    meta = get_session_meta(session_id)
    action_count = meta.get("action_count", "0")

    if not history:
        return {
            "score": 0,
            "feedback": "No actions were performed during this session.",
            "breakdown": "Nothing to evaluate.",
        }

    # Format history for the LLM
    formatted_history = ""
    for msg in history:
        role = msg["role"].upper()
        content = msg["content"]
        formatted_history += f"\n[{role}]: {content}\n"

    scoring_prompt = f"""
You are a strict but fair judge for a university data science competition.
A participant was given a messy CSV dataset and had to perform data cleaning and EDA tasks.
Below is their complete session history showing everything they did.

Total actions performed: {action_count}

=== SESSION HISTORY ===
{formatted_history}
=== END OF SESSION ===

Evaluate the participant's performance and give a score from 0 to 100 based on:

1. MISSING VALUE HANDLING (30 points)
   - Did they identify missing values? (df.isnull().sum() or similar)
   - Did they handle missing values using appropriate techniques? (median, mean, mode, drop)
   - Did they use the right technique for the right column type? (median for numeric, mode for categorical)

2. EXPLORATORY DATA ANALYSIS (40 points)
   - Did they explore the dataset shape and info? (df.shape, df.info(), df.describe())
   - Did they analyze distributions? (value_counts, histograms, etc.)
   - Did they look for patterns or correlations?
   - Did they generate any visualizations?

3. CODE QUALITY AND APPROACH (20 points)
   - Were their approaches logical and correct?
   - Did they verify results after operations?
   - Did they explore multiple angles?

4. DEPTH AND COMPLETENESS (10 points)
   - How thorough was their analysis overall?
   - Did they go beyond the basics?

IMPORTANT RULES:
- If the participant just asked the LLM to describe what good EDA looks like without actually doing it, heavily penalize them
- If they deleted all rows or destroyed the dataset, penalize heavily
- If they performed very few actions (less than 3), cap the score at 30
- Be strict but fair

Respond ONLY in this exact JSON format, nothing else:
{{
    "score": <integer 0-100>,
    "feedback": "<2-3 sentence overall feedback for the participant>",
    "breakdown": "<brief breakdown of points: missing values X/30, EDA X/40, code quality X/20, depth X/10>"
}}
"""

    raw = ask_claude(
        system="You are a strict but fair judge. Reply only with the JSON asked for.",
        messages=[{"role": "user", "content": scoring_prompt}],
        task="judge",
    )

    # Parse JSON response safely
    try:
        import json
        # Strip markdown fences if LLM added them
        if "```" in raw:
            raw = raw.split("```")[1]
            if raw.startswith("json"):
                raw = raw[4:]
        result = json.loads(raw.strip())
        return {
            "score": int(result.get("score", 0)),
            "feedback": result.get("feedback", "No feedback provided."),
            "breakdown": result.get("breakdown", "No breakdown provided."),
        }
    except Exception:
        return {
            "score": 0,
            "feedback": "Scoring failed due to a parsing error. Please contact the organizer.",
            "breakdown": raw,
        }