<footer class="text-center mt-5 mb-3 text-muted">
    &copy; <?= date('Y') ?> <?= $logged_in ? ', ' . htmlspecialchars($_SESSION['user']['name']) : '' ?> Բոլոր իրավունքները պաշտպանված են։
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>