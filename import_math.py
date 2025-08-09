# import math

# def solve_master_thoerem(a, b, k, p):
#     """Solves recurence relations of the form T(n) = a * T(n/b) + f (n), where f(n) is of the form n^k * (log n)^ p.
#     Args a(float): The factor by which the input size is reduced (must be > 1). k(float): The power of n in f(n).
#     p(float) the power of log(n) in f(n). Returns str: A. string describing the bound in Theta notation."""

#     if a < 1 or b <= 1:
#         return "Invalid input: 'a' must be > 1."
        
#     #Calculate log_b(a)
#     log_b_a = math.log(a, b)
#     print(f"Value of log_b_a is: {log_b_a: .4f}")
#     print(f"Comparing f(n) = n ^ {k} vs. n ^({log_b_a:.4f})")
#     print("-" * 30)

# # Case 1: f(n) is polynomially smaller than n^(log_b_a)
# # Case 2: f(n) is polynomially larger than n^(log_b_a)
#     if k < log_b_a:
#         #Check for a clean polynomial difference, We check if log_b_a is gisgnificantly greater than k
#         if not math.isclose(k, log_b_a):
#             print("Result: Case 1 applies.")
#             #If the difference is not close, we can return the exact bound
#         return f"Θ(n^{log_b_a: .4f})"

#     # Case 2: f(n) is asymptotically the same as n^(log_b_a)
#     if math.isclose(k, log_b_a): 
#         print ("Result: Case 2 applies.")
#         if p > -1:
#             return f"Θ(n^{log_b_a:.4f} * (log n) ^{p+1})"
#         elif p == -1:
#             return f"Θ(n^{log_b_a:.4f} * log(log n))"
#         else:
#             return f"Θ(n^ {log_b_a:.4f})"

#     # Case 3: f(n) is polynomially larger than n^(log_b_a)
#     if k > log_b_a:
#         # Check for a clean polynomial difference and the regularly condition
#         if not math.isclose(k, log_b_a):
#             print("Result: Case 3 applies")

#         #The regularity condition a * f(n/b) <= c * f(n) for c < 1 is assumed to hold for this f(n) form
#         if p >= 0:
#             return f"Θ(n^{k} * (log n)^{p})"
#         else:
#             return f"Θ(n^{k})"
    
#     return "Could not determine case. The master Theorem may not apply"




#   # --- So to solve the problem from the question ---
#   # The recurrence is T(n) = 3T(n/3) +n
#   # Simp;ied form T(n) = 3T(n/3) + n

#   # Identify parameters for the simplified form
#   # a = 3
#   # b = 3
#   # f(n) = n which is n^1 * (log n) ^0, So k =1, p= 0
# a = 3
# b = 3
# k = 1
# p = 0

# answer = solve_master_thoerem(a, b, k, p)
# print("\nFinal Answer for T(n) = 3T(n/3) + n is:") 
# print(answer)



# import math

# def brute_force_closest_pair(points):
#     n = len(points)
#     min_distance = float('inf')
#     closest_pair = None
    
#     for i in range(n):
#         for j in range(i + 1, n):
#             dist = math.dist(points[i], points[j])
#             if dist < min_distance:
#                 min_distance = dist
#                 closest_pair = (points[i], points[j])
    
#     return closest_pair, min_distance

# # Example usage:
# points = [(0, 0), (1, 1), (2, 3), (4, 5)]
# pair, distance = brute_force_closest_pair(points)
# print(f"Closest Pair: {pair}")
# print(f"  Distance: {distance}")


# import math

# def simple_closest_pair(points):
#     """
#     Finds the closest pair of points and their distance using a simple
#     brute-force algorithm that runs in O(n^2) time.

#     Args:
#         points: A list of points, where each point is a tuple (x, y).

#     Returns:
#         A tuple containing:
#         - The first point of the closest pair.
#         - The second point of the closest pair.
#         - The Euclidean distance between them.
#     """
#     if len(points) < 2:
#         return None, None, float('inf')

#     closest_pair = (None, None)
#     min_dist_sq = float('inf')

#     # 1. Iterate through each point in the list.
#     for i in range(len(points)):
#         # 2. For each point, iterate through the remaining points.
#         for j in range(i + 1, len(points)):
#             p1 = points[i]
#             p2 = points[j]
            
#             # 3. Calculate the squared distance between the two points.
#             dist_sq = (p1[0] - p2[0])**2 + (p1[1] - p2[1])**2
            
#             # 4. If this distance is smaller than the minimum found so far, update.
#             if dist_sq < min_dist_sq:
#                 min_dist_sq = dist_sq
#                 closest_pair = (p1, p2)

#     # 5. Calculate the actual distance from the minimum squared distance.
#     final_distance = math.sqrt(min_dist_sq)
    
#     # 6. Return the pair and their distance.
#     return closest_pair[0], closest_pair[1], final_distance

# point_list = [(2, 3), (12, 30), (40, 50), (5, 1), (12, 10), (3, 4)]
# p1, p2, distance = simple_closest_pair(point_list)

# print(f"This script correctly solves question 6(a).\n")
# print(f"The set of points is: {point_list}")
# if p1 and p2:
#     print(f"The closest pair is: {p1} and {p2}")
#     print(f"Their distance is: {distance:.4f}")
# else:
#     print("Could not find a pair. Need at least two points.")



import math

def closest_pair(points):
    # Sort points by x-coordinate once at the beginning
    Px = sorted(points, key=lambda p: p[0])
    # Also sort by y-coordinate (for efficient merge step)
    Py = sorted(points, key=lambda p: p[1])
    
    return closest_pair_recursive(Px, Py)

def closest_pair_recursive(Px, Py):
    n = len(Px)
    if n <= 3:
        return brute_force_closest_pair(Px)

    mid = n // 2
    Qx = Px[:mid]
    Rx = Px[mid:]

    midpoint = Px[mid][0]
    Qy = list(filter(lambda p: p[0] <= midpoint, Py))
    Ry = list(filter(lambda p: p[0] > midpoint, Py))

    # Recursively find closest pair in left and right halves
    dL = closest_pair_recursive(Qx, Qy)
    dR = closest_pair_recursive(Rx, Ry)

    # Find minimal distance across halves
    d = min(dL, dR)

    # Build strip around the middle line (points within d)
    strip = [p for p in Py if abs(p[0] - midpoint) < d]

    # Find the closest points in strip (O(n))
    d_strip = closest_in_strip(strip, d)

    return min(d, d_strip)

def brute_force_closest_pair(P):
    min_dist = math.inf
    n = len(P)
    for i in range(n):
        for j in range(i+1, n):
            dist = euclidean_distance(P[i], P[j])
            if dist < min_dist:
                min_dist = dist
    return min_dist

def closest_in_strip(strip, d):
    min_dist = d
    n = len(strip)
    for i in range(n):
        j = i + 1
        while j < n and (strip[j][1] - strip[i][1]) < min_dist:
            dist = euclidean_distance(strip[i], strip[j])
            if dist < min_dist:
                min_dist = dist
            j += 1
    return min_dist

def euclidean_distance(p1, p2):
    return math.hypot(p1[0] - p2[0], p1[1] - p2[1])

# Example Usage
points = [(2, 3), (12, 30), (40, 50), (5, 1), (12, 10), (3, 4)]
print("Closest Pair Distance:", closest_pair(points))
