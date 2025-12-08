<?php

// Function to check if a user is logged in
function isLoggedIn()
{
  return isset($_SESSION['user_id']);
}

// Function to check if the logged-in user is an Admin
function isAdmin()
{
  return isset($_SESSION['user_level']) && $_SESSION['user_level'] === 'Admin';
}
